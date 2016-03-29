<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
	  integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<link rel="stylesheet" href="../vendor/mesour/components/public/DateTimePicker/bootstrap-datetimepicker.min.css">

<style>
	.table.styled-table > tbody > tr > td,
	.table.styled-table > tbody > tr > th {
		border: none;
	}

	.styled-table th {
		width: 35%;
		text-align: left;
	}

	.styled-table td {
		width: 65%;
		border: none;
	}

	.fa-remove[data-editable] {
		color: #d43f3a;
	}

	.styled-table ul {
		padding-left: 0;
		list-style-type: none;
	}

	.add-new {
		display: inline-block;
		padding-top: 10px;
		cursor: pointer;
	}
</style>

<?php

define('SRC_DIR', __DIR__ . '/../src/');

require_once __DIR__ . '/../vendor/autoload.php';

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, __DIR__ . '/log');
\Tracy\Debugger::$strictMode = true;

$loader = new Nette\Loaders\RobotLoader;
$loader->addDirectory(SRC_DIR);
$loader->setCacheStorage(new Nette\Caching\Storages\FileStorage(__DIR__ . '/tmp'));
$loader->register();

// CONNECTION & NDBT

$connection = new \Nette\Database\Connection(
	'mysql:host=127.0.0.1;dbname=mesour_editable',
	'root',
	'root'
);

$cacheMemoryStorage = new \Nette\Caching\Storages\MemoryStorage();

$structure = new \Nette\Database\Structure($connection, $cacheMemoryStorage);
$conventions = new \Nette\Database\Conventions\DiscoveredConventions($structure);
$context = new \Nette\Database\Context($connection, $structure, $conventions, $cacheMemoryStorage);

// SELECTION

$selection = $context->table('users');
$selection->select('users.*')
	->select('group.name group_name')
	->select('group.type group_type')
	->select('group.date group_date');

// SOURCE

$source = new \Mesour\Sources\NetteDbTableSource('users', 'id', $selection, $context);

$source->addTableToStructure('user_companies', 'user_id');
$source->addTableToStructure('companies', 'id');

// SOURCE - DATE STRUCTURE

$dataStructure = $source->getDataStructure();

$dataStructure->addOneToOne('group_name', 'groups', 'name');
$dataStructure->addOneToOne('group_type', 'groups', 'type');
$dataStructure->addOneToOne('group_date', 'groups', 'date');

$dataStructure->addOneToMany(
	'addresses',
	'user_addresses',
	'user_id',
	'{street}, {zip} {city}, {country}'
);

$dataStructure->addManyToMany(
	'companies',
	'companies',
	'company_id',
	'user_companies',
	'user_id',
	'{street}, {zip} {city}, {country}'
);

// EDITABLE STRUCTURE

$structure = \Mesour\Editable\Structures\DataStructure::fromSource($source);

$groupsElement = $structure->getElement('groups');

$groupsElement->addText('name', 'Name');

$groupsElement->addText('type', 'Type');

$groupsElement->addDate('date', 'Date')
	->setFormat('Y-m-d H:i:s');

$groupsElement->addNumber('members', 'Members')
	->setUnit('EUR')
	->setDecimals(2)
	->setDecimalPoint(',')
	->setThousandSeparator('.');

$addressesElement = $structure->getElement('user_addresses');

$addressesElement->addText('street', 'Street');
$addressesElement->addText('city', 'City');
$addressesElement->addText('zip', 'Zip');
$addressesElement->addText('country', 'Country');

$companiesElement = $structure->getElement('companies');

$companiesElement->addText('name', 'Name');
$companiesElement->addText('reg_num', 'Reg. number');
$companiesElement->addBool('verified', 'Verified');

function createForUser(\Mesour\Editable\Structures\IDataStructure $structure, $userId)
{
	$structure->addText('name', 'Name', $userId);

	$structure->addText('surname', 'Surname', $userId);

	$structure->addText('email', 'Email', $userId);

	$structure->addNumber('amount', 'Amount', $userId)
		->setUnit('EUR')
		->setDecimalPoint(',')
		->setThousandSeparator('.')
		->setDecimals(2);

	$structure->addDate('last_login', 'Last login', $userId)
		->setFormat('Y-m-d H:i:s');

	$structure->addEnum('role', 'Role', $userId)
		->addValue('admin', 'Admin')
		->addValue('moderator', 'Moderator');

	$structure->addBool('has_pro', 'Has PRO', $userId);

	$structure->addOneToOne('group_name', 'Group', $userId)
		->enableCreateNewRow();

	$structure->addOneToMany('addresses', 'Addresses', $userId);

	$structure->addManyToMany('companies', 'Companies', $userId);
}

// APPLICATION

$application = new \Mesour\UI\Application('mesourApp');

$application->setRequest($_REQUEST);

$application->run();

// EDITABLE

$editable = new \Mesour\UI\Editable('editableTest', $application);

createForUser($structure, 1);

$editable->setDataStructure($structure);

$editable->onCreate[] = function (
	\Mesour\Editable\Structures\Fields\IStructureElementField $field,
	array $newValues,
	$identifier = null,
	array $params = []
) use ($context) {
	if ($field->getName() === 'companies') {
		if (!strlen(trim($newValues['name']))) {
			$exception = new \Mesour\Editable\ValidatorException('Company name is required');
			$exception->setFieldName('name');
			throw $exception;
		}
		$company = $context->table('companies')->insert($newValues);
		$context->table('user_companies')->insert(
			[
				'user_id' => $identifier,
				'company_id' => $company->getPrimary(),
			]
		);
	} elseif ($field->getName() === 'group_name') {
		if (!strlen(trim($newValues['name']))) {
			$exception = new \Mesour\Editable\ValidatorException('Group name is required');
			$exception->setFieldName('name');
			throw $exception;
		}
		$group = $context->table('groups')->insert($newValues);
		$context->table('users')
			->where('id = ?', $identifier)
			->update(
				[
					'group_id' => $group->getPrimary(),
				]
			);
	} elseif ($field->getName() === 'addresses') {
		if (!strlen(trim($newValues['country']))) {
			$exception = new \Mesour\Editable\ValidatorException('Country is required');
			$exception->setFieldName('country');
			throw $exception;
		}
		$context->table('user_addresses')->insert(
			array_merge(
				$newValues,
				[
					'user_id' => $identifier,
				]
			)
		);
	}
};

$editable->onRemove[] = function (
	\Mesour\Editable\Structures\Fields\IStructureElementField $field,
	$value,
	$identifier = null
) use ($context) {
	if ($field->getName() === 'companies') {
		$reference = $field->getReference();
		$companyPrimaryKey = $reference['self_column'];
		$userPrimaryKey = $reference['column'];
		$context->table('user_companies')
			->where($companyPrimaryKey . ' = ?', $value)
			->where($userPrimaryKey . ' = ?', $identifier)
			->delete();
	} elseif ($field->getName() === 'addresses') {
		$context->table('user_addresses')
			->where('id = ?', $value)
			->delete();
	}
};

$editable->onAttach[] = function (
	\Mesour\Editable\Structures\Fields\IStructureElementField $field,
	\Mesour\Editable\Structures\Reference $reference,
	$identifier = null,
	array $params = []
) use ($context) {
	if ($field->getName() === 'companies') {
		$context->table('user_companies')
			->insert(
				[
					$reference->getFromId() => $reference->getFromValue(),
					$reference->getToId() => $reference->getToValue(),
				]
			);
	}
};

$editable->onEditElement[] = function (
	\Mesour\Editable\Structures\Fields\IStructureElementField $field,
	array $values,
	array $oldValues,
	\Mesour\Editable\Structures\Reference $reference,
	$identifier = null,
	array $params = []
) use ($context) {
	if ($field->getName() === 'addresses') {
		unset($values['id']);
		$context->table('user_addresses')
			->where('id = ?', $reference->getToValue())
			->update($values);
	} elseif ($field->getName() === 'companies') {
		unset($values['id']);
		$context->table('companies')
			->where('id = ?', $reference->getToValue())
			->update($values);
	}
};

$editable->onEditField[] = function (
	\Mesour\Editable\Structures\Fields\IStructureField $field,
	$newValue,
	$oldValue,
	$identifier = null,
	array $params = []
) use ($context, $source) {
	if ($field->getName() === 'group_name') {
		$data = [
			'group_id' => $newValue,
		];
	} else {
		if ($field->getName() === 'email' && !\Nette\Utils\Validators::isEmail($newValue)) {
			$exception = new \Mesour\Editable\ValidatorException('Value must be valid email.');
			throw $exception;
		}
		$data = [
			$field->getName() => $newValue === false ? 0 : $newValue,
		];
	}
	$context->table('users')
		->where('id = ?', $identifier)
		->update($data);
};

echo $editable->render();

$currentUser = $source->fetch();
$currentUserId = $currentUser['id'];

?>

<hr>

<div class="container"<?php echo $editable->createSnippet()->attributes(); ?>>

	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">User detail</h3>
		</div>
		<div class="panel-body">
			<div class="row">

				<div class="col-lg-6">
					<table class="table styled-table">
						<tr>
							<th>Name</th>
							<td data-editable="name" data-id="<?php echo $currentUserId; ?>">
								<?php echo $currentUser['name']; ?>
							</td>
						</tr>
						<tr>
							<th>Surname</th>
							<td data-editable="surname" data-id="<?php echo $currentUserId; ?>">
								<?php echo $currentUser['surname']; ?>
							</td>
						</tr>
						<tr>
							<th>Email</th>
							<td data-editable="email" data-id="<?php echo $currentUserId; ?>">
								<?php echo $currentUser['email']; ?>
							</td>
						</tr>
						<tr>
							<th>Birth date</th>
							<td data-editable="last_login" data-id="<?php echo $currentUserId; ?>">
								<?php echo $currentUser['last_login']->format('Y-m-d H:i:s'); ?>
							</td>
						</tr>
						<tr>
							<th>Addresses</th>
							<td>
								<ul>
									<?php foreach ($currentUser['addresses'] as $address) : ?>
										<?php $addressString = $address['street'] . ', ' . $address['zip'] . ' ' . $address['city'] . ', ' . $address['country'] ?>
										<li data-editable="addresses" data-no-action="true">
										<span data-editable="addresses" data-id="<?php echo $currentUserId; ?>"
											  data-value="<?php echo $address['id']; ?>"><?php echo $addressString; ?></span>
											<a href="#" class="fa fa-remove" data-editable="addresses"
											   data-id="<?php echo $currentUserId; ?>"
											   data-value="<?php echo $address['id']; ?>"
											   data-confirm="Really delete address <?php echo $addressString; ?>?"
											   data-is-remove="true"></a>
										</li>
									<?php endforeach; ?>
									<li>
										<a data-editable="addresses" data-is-add="true"
										   data-id="<?php echo $currentUserId; ?>" class="add-new">
											<i class="fa fa-plus"></i>
											Add new address
										</a>
									</li>
								</ul>
							</td>
						</tr>
					</table>
				</div>

				<div class="col-lg-6">
					<table class="table styled-table">
						<tr>
							<th>Amount</th>
							<td data-editable="amount" data-id="<?php echo $currentUserId; ?>">
								<?php echo $currentUser['amount']; ?> EUR
							</td>
						</tr>
						<tr>
							<th>Role</th>
							<td data-editable="role" data-id="<?php echo $currentUserId; ?>"
								data-value="<?php echo $currentUser['role']; ?>">
								<?php echo $currentUser['role']; ?>
							</td>
						</tr>
						<tr>
							<th>Group</th>
							<td data-editable="group_name" data-id="<?php echo $currentUserId; ?>"
								data-value="<?php echo $currentUser['group_id']; ?>">
								<?php echo $currentUser['group_name']; ?>
							</td>
						</tr>
						<tr>
							<th>Has PRO</th>
							<td data-editable="has_pro" data-id="<?php echo $currentUserId; ?>"
								data-value="<?php echo (int) $currentUser['has_pro']; ?>">
								<?php echo($currentUser['has_pro'] ? '<b style="color:green">Yes</b>' : '<b style="color:red">No</b>'); ?>
							</td>
						</tr>
						<tr>
							<th>Companies</th>
							<td>
								<ul>
									<?php foreach ($currentUser['companies'] as $company) : ?>
										<li data-editable="companies" data-no-action="true">
										<span data-editable="companies" data-id="<?php echo $currentUserId; ?>"
											  data-value="<?php echo $company['id']; ?>"><?php echo $company['name']; ?></span>
											<a href="#" class="fa fa-remove" data-editable="companies"
											   data-id="<?php echo $currentUserId; ?>"
											   data-value="<?php echo $company['id']; ?>"
											   data-confirm="Really unattach company <?php echo $company['name']; ?>?"
											   data-is-remove="true"></a>
										</li>
									<?php endforeach; ?>
									<li>
										<a data-editable="companies" data-is-add="true"
										   data-id="<?php echo $currentUserId; ?>" class="add-new">
											<i class="fa fa-plus"></i>
											Add new address
										</a>
									</li>
								</ul>
							</td>
						</tr>
					</table>
				</div>

			</div>
		</div>
	</div>

</div>

<hr>

<!-- Latest compiled and minified JavaScript -->
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
		integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
		crossorigin="anonymous"></script>

<script src="../vendor/mesour/components/public/DateTimePicker/moment.min.js"></script>
<script src="../vendor/mesour/components/public/DateTimePicker/bootstrap-datetimepicker.min.js"></script>

<script src="../vendor/mesour/components/public/mesour.components.min.js"></script>

<script src="../vendor/mesour/modal/public/mesour.modal.min.js"></script>

<script src="../public/src/fields/mesour.editable.field.Text.js"></script>
<script src="../public/src/fields/mesour.editable.field.Enum.js"></script>
<script src="../public/src/fields/mesour.editable.field.Number.js"></script>
<script src="../public/src/fields/mesour.editable.field.Date.js"></script>
<script src="../public/src/fields/mesour.editable.field.Bool.js"></script>
<script src="../public/src/fields/mesour.editable.field.OneToOne.js"></script>
<script src="../public/src/fields/mesour.editable.field.OneToMany.js"></script>
<script src="../public/src/fields/mesour.editable.field.ManyToMany.js"></script>
<script src="../public/src/mesour.editable.Validators.js"></script>
<script src="../public/src/mesour.editable.FieldEditor.js"></script>
<script src="../public/src/mesour.editable.Editable.js"></script>
<script src="../public/src/mesour.editable.EditableModal.js"></script>
<script src="../public/src/mesour.editable.core.js"></script>

<script>
	$(function () {
		var COMPONENT_NAME = 'mesourApp-editableTest';

		$(document).on('click', '[data-editable]', function (e) {
			var $this = $(this);

			if ($this.attr('data-no-action')) {
				return;
			}

			var name = $this.attr('data-editable'),
				isRemove = $this.attr('data-is-remove'),
				isAdd = $this.attr('data-is-add'),
				value = $this.attr('data-value'),
				id = $this.attr('data-id');

			if (!isRemove && e.ctrlKey) {
				e.preventDefault();

				mesour.editable.getComponent(COMPONENT_NAME).edit(name, $this, id, value);
			} else if (isRemove || isAdd) {
				e.preventDefault();

				if (isAdd) {
					mesour.editable.getComponent(COMPONENT_NAME).newEntry(name, $this, id);
				} else {
					var confirmText = $this.attr('data-confirm');
					if (!confirmText || (confirmText && confirm(confirmText))) {
						mesour.editable.getComponent(COMPONENT_NAME).remove(name, $this, id, value);
					}
				}
			}
		});
	});
</script>
