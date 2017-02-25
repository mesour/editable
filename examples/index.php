<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
      integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<link rel="stylesheet" href="../node_modules/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css">

<link rel="stylesheet" href="../node_modules/mesour-editable/dist/css/mesour.editable.min.css">

<style>

</style>

<?php

define('SRC_DIR', __DIR__ . '/../src/');

require_once __DIR__ . '/../vendor/autoload.php';

@mkdir(__DIR__ . '/log');
@mkdir(__DIR__ . '/tmp');

if (file_exists(__DIR__ . '/environment.php')) {
	require_once __DIR__ . '/environment.php';
}

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, __DIR__ . '/log');
\Tracy\Debugger::$strictMode = true;

use \Mesour\Editable\Rules\RuleType;

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
$selection->select('users.*');

// SOURCE

$source = new \Mesour\Sources\NetteDbTableSource('users', 'id', $selection, $context);

$dataStructure = $source->getDataStructure();

$dataStructure->renameColumn('groups', 'group');
$dataStructure->renameColumn('user_addresses', 'addresses');
$dataStructure->renameColumn('wallets', 'wallet');

/** @var \Mesour\Sources\Structures\Columns\ManyToOneColumnStructure $groupColumn */
$groupColumn = $dataStructure->getColumn('group');
$groupColumn->setPattern('{name} ({members})');

/** @var \Mesour\Sources\Structures\Columns\OneToManyColumnStructure $addressColumn */
$addressColumn = $dataStructure->getColumn('addresses');
$addressColumn->setPattern('{street}, {zip} {city}, {country}');

/** @var \Mesour\Sources\Structures\Columns\ManyToManyColumnStructure $companiesColumn */
$companiesColumn = $dataStructure->getColumn('companies');
$companiesColumn->setPattern('{name}');

/** @var \Mesour\Sources\Structures\Columns\OneToOneColumnStructure $walletColumn */
$walletColumn = $dataStructure->getColumn('wallet');
$walletColumn->setPattern('{amount}');

// EDITABLE STRUCTURE

$structure = \Mesour\Editable\Structures\DataStructure::fromSource($source);

$structure->addText('name', 'Name')
	->addRule(RuleType::EMAIL, 'Test email message.');

$groupsElement = $structure->getElement('groups');

$groupsElement->addText('name', 'Name')
	->addRule(RuleType::PATTERN, 'Test email message.', '[0-9a-z]{6}');

$groupsElement->addText('type', 'Type');

$groupsElement->addDate('date', 'Date')
	->setFormat('Y-m-d H:i:s');

$groupsElement->addNumber('members', 'Members')
	->addRule(RuleType::RANGE, 'Rule type range error', [0, 50])
	->setUnit('EUR')
	->setDecimals(2)
	->setDecimalPoint(',')
	->setThousandSeparator('.');

$addressesElement = $structure->getElement('user_addresses');

$addressesElement->addText('street', 'Street')
	->setTextarea();

$addressesElement->addText('city', 'City');
$addressesElement->addText('zip', 'Zip');
$addressesElement->addText('country', 'Country');

$companiesElement = $structure->getElement('companies');

$companiesElement->addText('name', 'Name');
$companiesElement->addText('reg_num', 'Reg. number');
$companiesElement->addBool('verified', 'Verified');

$walletElement = $structure->getElement('wallets');

$walletElement->addNumber('amount', 'Amount')
	->setDecimalPoint(',')
	->setThousandSeparator('.')
	->setDecimals(2);

$walletElement->addEnum('currency', 'Currency');

function createForUser(\Mesour\Editable\Structures\IDataStructure $structure, $userId)
{
	$structure->addText('name', 'Name', $userId)
		->setTextarea()
		->addRule(\Mesour\Editable\Rules\RuleType::FILLED, 'Test error message.')
		->setEditPermission('user-editable', 'name-edit');

	$structure->addText('surname', 'Surname', $userId)
		->setEditPermission('user-editable', 'surname-edit');

	$structure->addText('email', 'Email', $userId)
		->setEditPermission('user-editable', 'email-edit');

	$structure->addNumber('amount', 'Amount', $userId)
		->setUnit('EUR')
		->setDecimalPoint(',')
		->setThousandSeparator('.')
		->setDecimals(2)
		->setEditPermission('user-editable', 'amount-edit');

	$structure->addDate('last_login', 'Last login', $userId)
		->setFormat('Y-m-d H:i:s')
		->setEditPermission('user-editable', 'last_login-edit');

	$structure->addEnum('role', 'Role', $userId)
		->addValue('admin', 'Admin')
		->addValue('moderator', 'Moderator')
		->setEditPermission('user-editable', 'role-edit');

	$structure->addBool('has_pro', 'Has PRO', $userId)
		->setDescription('Has PRO')
		->setEditPermission('user-editable', 'has_pro-edit');

	$structure->addOneToOne('wallet', 'Wallet', $userId)
		->enableRemoveRow()
		->enableCreateNewRow()
		->setEditPermission('user-editable', 'wallet-edit');

	$structure->addManyToOne('group', 'Group', $userId)
		->enableEditCurrentRow()
		->enableEditCurrentRow()
		->enableCreateNewRow()
		->setEditPermission('user-editable', 'group-edit')
		->setRemovePermission('user-editable', 'group-remove')
		->setCreatePermission('user-editable', 'group-create');

	$structure->addOneToMany('addresses', 'Addresses', $userId)
		->enableCreateNewRow()
		->enableRemoveRow()
		->setEditPermission('user-editable', 'addresses-edit')
		->setRemovePermission('user-editable', 'addresses-remove')
		->setCreatePermission('user-editable', 'addresses-create');

	$structure->addManyToMany('companies', 'Companies', $userId)
		->enableCreateNewRow()
		->enableRemoveRow()
		->enableAttachRow()
		->setEditPermission('user-editable', 'companies-edit')
		->setRemovePermission('user-editable', 'companies-remove')
		->setAttachPermission('user-editable', 'companies-attach')
		->setCreatePermission('user-editable', 'companies-create');
}

// APPLICATION

$application = new \Mesour\UI\Application('mesourApp');

$application->setRequest($_REQUEST);

$application->setUserRole('admin');

$auth = $application->getAuthorizator();

$auth->addRole('guest');
$auth->addRole('registered', 'guest');
$auth->addRole('admin', 'registered');

$auth->addResource('user-editable');

$auth->allow('admin', 'user-editable');

$auth->deny('registered', 'user-editable', 'companies-edit');
$auth->deny('registered', 'user-editable', 'companies-create');
$auth->deny('registered', 'user-editable', 'companies-remove');
$auth->deny('registered', 'user-editable', 'companies-attach');

$application->run();

// EDITABLE

$editable = new \Mesour\UI\Editable('editableTest', $application);

createForUser($structure, 1);

$editable->setDataStructure($structure);

//$editable->setInline();
//$editable->disableInlineAlerts();

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
	} elseif ($field->getName() === 'group') {
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
	} elseif ($field->getName() === 'wallet') {
		$newValues['user_id'] = $identifier;
		$wallet = $context->table('wallets')->insert($newValues);
		$context->table('users')
			->where('id = ?', $identifier)
			->update(
				[
					'wallet_id' => $wallet->getPrimary(),
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
	} elseif ($field->getName() === 'wallet') {
		$context->table('wallets')
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
	} elseif ($field->getName() === 'group') {
		unset($values['id']);
		$context->table('groups')
			->where('id = ?', $reference->getToValue())
			->update($values);
	} elseif ($field->getName() === 'wallet') {
		unset($values['id'], $values['wallet_id']);
		$context->table('wallets')
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
	if ($field->getName() === 'group') {
		$data = [
			'group_id' => !$newValue ? null : $newValue,
		];
	} else {
		if ($field->getName() === 'email' && !\Nette\Utils\Validators::isEmail($newValue)) {
			$exception = new \Mesour\Editable\ValidatorException('Value must be valid email.');
			$exception->setFieldName($field->getName());
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

$created = $editable->create();

$currentUser = $source->fetch();
$currentUserId = $currentUser['id'];

?>

<hr style="margin-bottom: 50px;">

<div class="container"<?php echo $editable->createSnippet()->attributes(); ?>>

	<?php echo $created; ?>

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
							<td data-editable="name" data-id="<?php echo $currentUserId; ?>" title="Enter firstname">
								<?php echo $currentUser['name']; ?>
							</td>
						</tr>
						<tr>
							<th>Surname</th>
							<td data-editable="surname" data-id="<?php echo $currentUserId; ?>" title="Enter lastname">
								<?php echo $currentUser['surname']; ?>
							</td>
						</tr>
						<tr>
							<th>Email</th>
							<td data-editable="email" data-id="<?php echo $currentUserId; ?>" title="Enter email">
								<?php echo $currentUser['email']; ?>
							</td>
						</tr>
						<tr>
							<th>Birth date</th>
							<?php $lastLogin = $currentUser['last_login'] ? $currentUser['last_login']->format(
								'Y-m-d H:i:s'
							) : null; ?>
							<td data-editable="last_login" data-id="<?php echo $currentUserId; ?>" data-value="<?php echo $lastLogin; ?>" title="Enter birth date">
								<?php echo $lastLogin ? $lastLogin : '<i>none</i>'; ?>
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
							<td data-editable="amount" data-id="<?php echo $currentUserId; ?>" title="Enter amount">
								<?php
								echo $currentUser['amount']
									? (number_format($currentUser['amount'], 2, ',', '.') . ' EUR')
									: '<i>null</i>';
								?>
							</td>
						</tr>
						<tr>
							<th>Role</th>
							<td data-editable="role" data-id="<?php echo $currentUserId; ?>"
							    data-value="<?php echo $currentUser['role']; ?>" title="Select role">
								<?php echo $currentUser['role'] ? ucfirst($currentUser['role']) : '<i>none</i>'; ?>
							</td>
						</tr>
						<tr>
							<th>Group</th>
							<td data-editable="group" data-id="<?php echo $currentUserId; ?>"
							    data-value="<?php echo $currentUser['group_id']; ?>" title="Select group">
								<?php echo $currentUser['group'] && count(
									$currentUser['group']
								) > 0 ? $currentUser['group']['_pattern'] : '<i>none</i>'; ?>
								<?php if (!$currentUser['group']) { ?>
									<i class="fa fa-plus"></i>
									<span data-editable="group" data-is-add="true" title="Select group"
									      data-id="<?php echo $currentUserId; ?>" class="attach">
										Attach group
									</span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<th>Has PRO</th>
							<td data-editable="has_pro" data-id="<?php echo $currentUserId; ?>"
							    data-value="<?php echo (int) $currentUser['has_pro']; ?>" title="Set PRO">
								<?php echo(is_null($currentUser['has_pro'])
									? '<i>none</i>' : ($currentUser['has_pro'] ? '<b style="color:green">Yes</b>' : '<b style="color:red">No</b>')); ?>
							</td>
						</tr>
						<tr>
							<th>Wallet</th>
							<td data-editable="wallet" data-no-action="true">
								<span data-editable="wallet" data-id="<?php echo $currentUserId; ?>"
								      data-value="<?php echo $currentUser['wallet_id']; ?>">
									<?php echo count(
										$currentUser['wallet']
									) > 0 ? $currentUser['wallet']['_pattern'] : ''; ?></span>
								<?php if (!count($currentUser['wallet'])) { ?>
									<i class="fa fa-plus"></i>
									<span data-editable="wallet" data-is-add="true" title="Create new wallet"
									      data-id="<?php echo $currentUserId; ?>" class="attach">
										Create new wallet
									</span>
								<?php } else { ?>
									<a href="#" class="fa fa-remove" data-editable="wallet"
									   data-id="<?php echo $currentUserId; ?>"
									   data-value="<?php echo $currentUser['wallet_id']; ?>"
									   data-confirm="Really remove wallet with: <?php echo $currentUser['wallet']['amount'] . ' ' . $currentUser['wallet']['currency']; ?>?"
									   data-is-remove="true"></a>
								<?php } ?>
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

<script src="../node_modules/eonasdan-bootstrap-datetimepicker/node_modules/moment/min/moment.min.js"></script>
<script src="../node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>

<script src="../node_modules/mesour-editable/dist/js/mesour.editable.js"></script>

<script>
	$(function() {
		var COMPONENT_NAME = 'mesourApp-editableTest';

		$(document).on('click', '[data-editable]', function(e) {
			var $this = $(this);

			if ($this.attr('data-no-action')) {
				return;
			}

			var name = $this.attr('data-editable'),
				isRemove = $this.attr('data-is-remove'),
				isAdd = $this.attr('data-is-add'),
				value = $this.attr('data-value'),
				id = $this.attr('data-id');

			if (!isRemove && (e.ctrlKey || e.metaKey)) {
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

			mesour.editable.getComponent(COMPONENT_NAME).setOnCompleteCallback(function(fieldName) {
				var $this = $('[data-editable="' + fieldName + '"]');

				$this.css('background-color', '#bce8f1');
				$this.animate({ backgroundColor: "#fff" }, 1200);
			});
		});
	});

	/**!
	 * @preserve Color animation 1.6.0
	 * http://www.bitstorm.org/jquery/color-animation/
	 * Copyright 2011, 2013 Edwin Martin
	 * Released under the MIT and GPL licenses.
	 */

	(function($) {
		/**
		 * Check whether the browser supports RGBA color mode.
		 *
		 * Author Mehdi Kabab <http://pioupioum.fr>
		 * @return {boolean} True if the browser support RGBA. False otherwise.
		 */
		function isRGBACapable() {
			var $script = $('script:first'),
				color = $script.css('color'),
				result = false;
			if (/^rgba/.test(color)) {
				result = true;
			} else {
				try {
					result = ( color != $script.css('color', 'rgba(0, 0, 0, 0.5)').css('color') );
					$script.css('color', color);
				} catch (e) {
				}
			}

			return result;
		}

		$.extend(true, $, {
			support: {
				'rgba': isRGBACapable()
			}
		});

		var properties = ['color', 'backgroundColor', 'borderBottomColor', 'borderLeftColor', 'borderRightColor', 'borderTopColor', 'outlineColor'];
		$.each(properties, function(i, property) {
			$.Tween.propHooks[ property ] = {
				get: function(tween) {
					return $(tween.elem).css(property);
				},
				set: function(tween) {
					var style = tween.elem.style;
					var p_begin = parseColor($(tween.elem).css(property));
					var p_end = parseColor(tween.end);
					tween.run = function(progress) {
						style[property] = calculateColor(p_begin, p_end, progress);
					}
				}
			}
		});

		// borderColor doesn't fit in standard fx.step above.
		$.Tween.propHooks.borderColor = {
			set: function(tween) {
				var style = tween.elem.style;
				var p_begin = [];
				var borders = properties.slice(2, 6); // All four border properties
				$.each(borders, function(i, property) {
					p_begin[property] = parseColor($(tween.elem).css(property));
				});
				var p_end = parseColor(tween.end);
				tween.run = function(progress) {
					$.each(borders, function(i, property) {
						style[property] = calculateColor(p_begin[property], p_end, progress);
					});
				}
			}
		}

		// Calculate an in-between color. Returns "#aabbcc"-like string.
		function calculateColor(begin, end, pos) {
			var color = 'rgb' + ($.support['rgba'] ? 'a' : '') + '('
				+ parseInt((begin[0] + pos * (end[0] - begin[0])), 10) + ','
				+ parseInt((begin[1] + pos * (end[1] - begin[1])), 10) + ','
				+ parseInt((begin[2] + pos * (end[2] - begin[2])), 10);
			if ($.support['rgba']) {
				color += ',' + (begin && end ? parseFloat(begin[3] + pos * (end[3] - begin[3])) : 1);
			}
			color += ')';
			return color;
		}

		// Parse an CSS-syntax color. Outputs an array [r, g, b]
		function parseColor(color) {
			var match, quadruplet;

			// Match #aabbcc
			if (match = /#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/.exec(color)) {
				quadruplet = [parseInt(match[1], 16), parseInt(match[2], 16), parseInt(match[3], 16), 1];

				// Match #abc
			} else if (match = /#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/.exec(color)) {
				quadruplet = [parseInt(match[1], 16) * 17, parseInt(match[2], 16) * 17, parseInt(match[3], 16) * 17, 1];

				// Match rgb(n, n, n)
			} else if (match = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color)) {
				quadruplet = [parseInt(match[1]), parseInt(match[2]), parseInt(match[3]), 1];

			} else if (match = /rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9\.]*)\s*\)/.exec(color)) {
				quadruplet = [parseInt(match[1], 10), parseInt(match[2], 10), parseInt(match[3], 10),parseFloat(match[4])];

				// No browser returns rgb(n%, n%, n%), so little reason to support this format.
			} else {
				quadruplet = colors[color];
			}
			return quadruplet;
		}

		// Some named colors to work with, added by Bradley Ayers
		// From Interface by Stefan Petre
		// http://interface.eyecon.ro/
		var colors = {
			'aqua': [0,255,255,1],
			'azure': [240,255,255,1],
			'beige': [245,245,220,1],
			'black': [0,0,0,1],
			'blue': [0,0,255,1],
			'brown': [165,42,42,1],
			'cyan': [0,255,255,1],
			'darkblue': [0,0,139,1],
			'darkcyan': [0,139,139,1],
			'darkgrey': [169,169,169,1],
			'darkgreen': [0,100,0,1],
			'darkkhaki': [189,183,107,1],
			'darkmagenta': [139,0,139,1],
			'darkolivegreen': [85,107,47,1],
			'darkorange': [255,140,0,1],
			'darkorchid': [153,50,204,1],
			'darkred': [139,0,0,1],
			'darksalmon': [233,150,122,1],
			'darkviolet': [148,0,211,1],
			'fuchsia': [255,0,255,1],
			'gold': [255,215,0,1],
			'green': [0,128,0,1],
			'indigo': [75,0,130,1],
			'khaki': [240,230,140,1],
			'lightblue': [173,216,230,1],
			'lightcyan': [224,255,255,1],
			'lightgreen': [144,238,144,1],
			'lightgrey': [211,211,211,1],
			'lightpink': [255,182,193,1],
			'lightyellow': [255,255,224,1],
			'lime': [0,255,0,1],
			'magenta': [255,0,255,1],
			'maroon': [128,0,0,1],
			'navy': [0,0,128,1],
			'olive': [128,128,0,1],
			'orange': [255,165,0,1],
			'pink': [255,192,203,1],
			'purple': [128,0,128,1],
			'violet': [128,0,128,1],
			'red': [255,0,0,1],
			'silver': [192,192,192,1],
			'white': [255,255,255,1],
			'yellow': [255,255,0,1],
			'transparent': [255,255,255,0]
		};
	})(jQuery);
</script>
