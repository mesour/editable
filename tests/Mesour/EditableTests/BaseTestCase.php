<?php

namespace Mesour\EditableTests;

use Mesour\Sources\Tests\DataSourceTestCase;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Database;

class BaseTestCase extends DataSourceTestCase
{

	/** @var Database\Connection */
	protected $connection;

	/** @var Database\Context */
	protected $context;

	/** @var Database\Table\Selection */
	protected $user;

	public function __construct($setConfigFiles = true)
	{
		if ($setConfigFiles) {
			$this->configFile = __DIR__ . '/../../config.php';
			$this->localConfigFile = __DIR__ . '/../../config.local.php';
		}

		parent::__construct();
	}

	protected function setUp()
	{
		parent::setUp();

		$this->connection = new Database\Connection(
			$this->baseConnection->getDsn(),
			$this->databaseFactory->getUserName(),
			$this->databaseFactory->getPassword()
		);

		$cacheMemoryStorage = new MemoryStorage();

		$structure = new Database\Structure($this->connection, $cacheMemoryStorage);
		$conventions = new Database\Conventions\DiscoveredConventions($structure);
		$this->context = new Database\Context($this->connection, $structure, $conventions, $cacheMemoryStorage);

		$this->user = $this->context->table('users');
	}

	/**
	 * @param string $handler
	 * @param array $request
	 * @return \Mesour\UI\Application
	 */
	protected function createApplication($handler = null, array $request = [])
	{
		$application = new \Mesour\UI\Application('mesourApp');

		if ($handler) {
			list($componentName, $handlerName) = explode('-', $handler);

			$newRequest = [];
			foreach ($request as $key => $value) {
				$newRequest['m_mesourApp-' . $componentName . '-' . $key] = $value;
			}
			$request = $newRequest;

			$request['m_do'] = 'mesourApp-' . $handler;
		}

		$application->setRequest($request);
		$application->run();

		return $application;
	}

	protected function createSource()
	{
		$source = new \Mesour\Sources\NetteDbTableSource('users', 'id', $this->user, $this->context);

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

		return $source;
	}

	protected function createDataStructure()
	{
		$structure = \Mesour\Editable\Structures\DataStructure::fromSource($this->createSource());

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

		return $structure;
	}

}
