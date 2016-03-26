<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
	  integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<link rel="stylesheet" href="../vendor/mesour/components/public/DateTimePicker/bootstrap-datetimepicker.min.css">

<?php

define('SRC_DIR', __DIR__ . '/../src/');

require_once __DIR__ . '/../vendor/autoload.php';

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, __DIR__ . '/log');
\Tracy\Debugger::$strictMode = true;

$loader = new Nette\Loaders\RobotLoader;
$loader->addDirectory(SRC_DIR);
$loader->setCacheStorage(new Nette\Caching\Storages\FileStorage(__DIR__ . '/tmp'));
$loader->register();

?>

<hr>

<div class="container">
	<h2>Without settings</h2>

	<?php

	$application = new \Mesour\UI\Application('mesourApp');

	$application->setRequest($_REQUEST);

	$application->run();

	$editable = new \Mesour\UI\Editable('editableTest', $application);

	$structure = new \Mesour\Editable\Structures\DataElementStructure();

	$structure->addText('name');

	$structure->addNumber('amount')
		->setDecimalPoint(',')
		->setThousandSeparator('.')
		->setDecimals(2)
		->setUnit('EUR');

	$structure->addDate('last_login')
		->setFormat('Y-m-d H:i:s');

	$structure->addEnum('role')
		->addValue('admin', 'Admin')
		->addValue('moderator', 'Moderator');

	$structure->addBool('has_pro');

	$editable->setDataStructure($structure);

	echo $editable->render();

	?>


	<div class="jumbotron">

		<p data-editable="name">-</p>
		<p data-editable="amount">100 EUR</p>
		<p data-editable="last_login">2016-01-01 20:00:00</p>
		<p data-editable="role" data-value="admin">Admin</p>
		<p data-editable="has_pro" data-value="1">Yes</p>

	</div>

</div>

<hr>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<!-- Latest compiled and minified JavaScript -->
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
		integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
		crossorigin="anonymous"></script>

<script src="../vendor/mesour/components/public/DateTimePicker/moment.min.js"></script>
<script src="../vendor/mesour/components/public/DateTimePicker/bootstrap-datetimepicker.min.js"></script>

<script src="../vendor/mesour/components/public/mesour.components.min.js"></script>

<script src="../vendor/mesour/modal/public/mesour.modal.min.js"></script>

<script src="../public/mesour.editable.min.js"></script>

<script>
	$(function () {
		var COMPONENT_NAME = 'mesourApp-editableTest';

		$('[data-editable]').on('click', function (e) {
			if (e.ctrlKey) {
				e.preventDefault();

				var $this = $(this),
					name = $this.attr('data-editable'),
					value = $this.attr('data-value');

				mesour.editable.getComponent(COMPONENT_NAME).edit(name, $this, null, value);
			}
		});
	});
</script>