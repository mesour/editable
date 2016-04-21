<?php

define('SRC_DIR', __DIR__ . '/../src/');
define('DISABLE_AUTOLOAD', true);

require_once __DIR__ . '/../vendor/autoload.php';

@mkdir(__DIR__ . "/log");
@mkdir(__DIR__ . "/../tmp");

if (file_exists(__DIR__ . '/environment.php')) {
	require_once __DIR__ . '/environment.php';
}

$loader = new Nette\Loaders\RobotLoader;
$loader->addDirectory(__DIR__ . '/../src');
$loader->addDirectory(__DIR__ . '/../vendor/mesour/sources/tests/classes');
$loader->addDirectory(__DIR__ . '/../vendor/mesour/sources/tests/Entity');
$loader->setCacheStorage(new Nette\Caching\Storages\FileStorage(__DIR__ . '/../tmp'));
$loader->register();

if (!class_exists('Tester\Assert')) {
	echo "Install Nette Tester using `composer update --dev`\n";
	exit(1);
}

define("TEMP_DIR", __DIR__ . '/../tmp');

Tester\Helpers::purge(TEMP_DIR);

Tester\Environment::setup();