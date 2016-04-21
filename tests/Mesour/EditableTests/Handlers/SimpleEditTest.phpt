<?php

namespace Mesour\EditableTests\Handlers;

use Mesour\Editable\Structures\Fields\TextField;
use Mesour\EditableTests\BaseTestCase;
use Mesour\UI\Editable;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

class SimpleEditTest extends BaseTestCase
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
			'name' => 'name',
			'identifier' => 1,
			'params' => [
				'id' => 1,
			],
			'newValue' => 'Peter',
			'oldValue' => 'John',
		];
		$application = $this->createApplication('editableTest-edit', $request);

		$application->addComponent($editable);

		$editable->onEditField[] = function (
			TextField $field,
			$newValue,
			$oldValue = null,
			$identifier = null,
			array $params = []
		) use ($editable) {
			Assert::same($editable->getDataStructure()->getField('name'), $field);
			Assert::same('Peter', $newValue);
			Assert::same('John', $oldValue);
			Assert::same(1, $identifier);
			Assert::same(['id' => 1], $params);
		};

		$editable->create();
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

$test = new SimpleEditTest();
$test->run();
