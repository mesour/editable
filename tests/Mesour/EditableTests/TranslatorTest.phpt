<?php

namespace Mesour\EditableTests;

use Mesour\Components\Localization\ITranslator;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/BaseTestCase.php';
require_once __DIR__ . '/TestTranslator.php';

class TranslatorTest extends BaseTestCase
{

	public function testDefault()
	{
		$editable = $this->createSampleEditable();

		$application = $this->createApplication();

		$application->getContext()->setService(new TestTranslator($this->getTranslates()), ITranslator::class);

		$application->addComponent($editable);

		Assert::same(
			file_get_contents(__DIR__ . '/data/TranslatorTestOutput.html'),
			$editable->render(),
			'Output of editable render doest not match'
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

	private function getTranslates()
	{
		return [
			'Select...' => '__translate_01',
			'Select one' => '__translate_02',
			'Select from existing' => '__translate_03',
			'All existing companies are attachet to this client...' => '__translate_04',
			'Attach existing' => '__translate_05',
			'Create new' => '__translate_06',
			'Successfuly saved' => '__translate_07',
			'Date is invalid' => '__translate_08',
			'Please select one item' => '__translate_09',
			'Value must be valid number' => '__translate_10',
			'You have not permission for this action.' => '__translate_11',
			'ERROR! Status: %status%. Try save data later.' => '__translate_12',
			'- none' => '__translate_13',
			'Really save empty value?' => '__translate_14',
			'Set empty value' => '__translate_15',
			'Save' => '__translate_16',
			'Cancel' => '__translate_17',
			'Edit in form' => '__translate_18',
		];
	}

}

$test = new TranslatorTest();
$test->run();
