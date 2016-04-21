<?php

namespace Mesour\EditableTests\Handlers;

use Mesour\Editable\Structures\Fields\BaseElementField;
use Mesour\Editable\Structures\Fields\TextField;
use Mesour\Editable\Structures\PermissionsChecker;
use Mesour\Editable\ValidatorException;
use Mesour\EditableTests\BaseTestCase;
use Mesour\InvalidStateException;
use Mesour\UI\Editable;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

class RemoveDisabledTest extends BaseTestCase
{

	public function setUp()
	{
		Editable::$debugMode = true;
		parent::setUp();
	}

	public function testDefault()
	{
		$editable = $this->createSampleEditable();

		$request = [
			'name' => 'addresses',
			'identifier' => 1,
			'value' => 10,
		];
		$application = $this->createApplication('editableTest-remove', $request);

		$application->addComponent($editable);

		$editable->onCreate[] = function () {
			Assert::false(true, 'Event can not be called');
		};

		Assert::exception(
			function () use ($editable) {
				$editable->create();
			},
			InvalidStateException::class
		);
	}

	private function createSampleEditable()
	{
		$structure = $this->createDataStructure();

		$userId = 1;

		$structure->addText('name', 'Name', $userId)
			->setTextarea();

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

		$structure->addBool('has_pro', 'Has PRO', $userId)
			->setDescription('Has PRO');

		$structure->addOneToOne('wallet', 'Wallet', $userId);

		$structure->addManyToOne('group', 'Group', $userId);

		$structure->addOneToMany('addresses', 'Addresses', $userId);

		$structure->addManyToMany('companies', 'Companies', $userId);

		$editable = new \Mesour\UI\Editable('editableTest');

		$editable->setDataStructure($structure);

		return $editable;
	}

}

$test = new RemoveDisabledTest();
$test->run();
