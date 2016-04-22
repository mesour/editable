<?php

namespace Mesour\EditableTests\Handlers;

use Mesour\Editable\Structures\Reference;
use Mesour\EditableTests\BaseTestCase;
use Mesour\UI\Editable;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

class AttachTest extends BaseTestCase
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
			'name' => 'companies',
			'identifier' => 1,
			'params' => [
				'id' => 1,
				'attach_row' => 1,
				'create_new_row' => 0,
				'remove_row' => 0,
			],
			'reference' => [
				'selfColumn' => [
					'name' => 'company_id',
					'value' => 7,
				],
				'column' => [
					'name' => 'user_id',
					'value' => 1,
				],
			],
		];
		$application = $this->createApplication('editableTest-attach', $request);

		$application->addComponent($editable);

		$editable->onAttach[] = function (
			\Mesour\Editable\Structures\Fields\IStructureElementField $field,
			\Mesour\Editable\Structures\Reference $reference,
			$identifier = null,
			array $params = []
		) use ($editable) {
			Assert::same($editable->getDataStructure()->getField('companies'), $field);
			Assert::equal(new Reference('user_id', 'company_id', 1, 7), $reference);
			Assert::same(1, $identifier);
			Assert::same(
				[
					'id' => 1,
					'attach_row' => 1,
					'create_new_row' => 0,
					'remove_row' => 0,
				],
				$params
			);
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

		$structure->addManyToMany('companies', 'Companies', $userId)
			->enableAttachRow();

		$editable = new \Mesour\UI\Editable('editableTest');

		$editable->setDataStructure($structure);

		return $editable;
	}

}

$test = new AttachTest();
$test->run();
