<?php

namespace Mesour\EditableTests\Handlers;

use Mesour\EditableTests\BaseTestCase;
use Mesour\UI\Editable;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

class CreateTest extends BaseTestCase
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
			'name' => 'group',
			'params' => [
				'id' => 1,
				'edit_current_row' => 0,
				'create_new_row' => 1,
				'remove_row' => 0,
			],
			'identifier' => 1,
			'values' => [
				'name' => 'New group',
				'type' => 'first',
				'date' => '2016-01-01',
				'members' => 116131,
			],
		];
		$application = $this->createApplication('editableTest-create', $request);

		$application->addComponent($editable);

		$editable->onCreate[] = function (
			\Mesour\Editable\Structures\Fields\IStructureElementField $field,
			array $newValues,
			$identifier = null,
			array $params = []
		) use ($editable) {
			Assert::same($editable->getDataStructure()->getField('group'), $field);
			Assert::equal(
				[
					'name' => 'New group',
					'type' => 'first',
					'date' => new \DateTime('2016-01-01'),
					'members' => 116131.0,
				],
				$newValues
			);
			Assert::same(1, $identifier);
			Assert::same([
				'id' => 1,
				'edit_current_row' => 0,
				'create_new_row' => 1,
				'remove_row' => 0,
			], $params);
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

		$structure->addManyToOne('group', 'Group', $userId)
			->enableCreateNewRow();

		$structure->addOneToMany('addresses', 'Addresses', $userId);

		$structure->addManyToMany('companies', 'Companies', $userId);

		$editable = new \Mesour\UI\Editable('editableTest');

		$editable->setDataStructure($structure);

		return $editable;
	}

}

$test = new CreateTest();
$test->run();
