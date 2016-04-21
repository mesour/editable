<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2016 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\Editable\Structures\Fields;

use Mesour;

/**
 * @author Matouš Němec (http://mesour.com)
 */
class ManyToManyField extends BaseElementField implements IManyToManyField
{

	private $attachRow = false;

	private $attachPermission;

	public function __construct($name)
	{
		parent::__construct($name);

		$this->enableCreateNewRow();
	}

	public function enableAttachRow()
	{
		$this->attachRow = true;
		return $this;
	}

	public function disableAttachRow()
	{
		$this->attachRow = false;
		return $this;
	}

	public function hasAttachRowEnabled()
	{
		return $this->attachRow;
	}

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return $this
	 */
	public function setAttachPermission($resource, $privilege)
	{
		$this->attachPermission = [$resource, $privilege];
		return $this;
	}

	/**
	 * @param string $role
	 * @param Mesour\Components\Security\IAuthorizator $authorizator
	 * @return bool
	 */
	public function isAllowedAttach($role, Mesour\Components\Security\IAuthorizator $authorizator)
	{
		return $this->checkIsAllowed($this->attachPermission, $role, $authorizator);
	}

	public function setReference(
		$table,
		$primaryKey,
		$referencedColumn,
		$pattern = null,
		$selfColumn = null,
		$referencedTable = null
	) {
		parent::setReference($table, $primaryKey, $referencedColumn, $pattern);
		$this->reference['self_column'] = $selfColumn;
		$this->reference['referenced_table'] = $referencedTable;
		return $this;
	}

	public function toArray()
	{
		$this->setParameter('attach_row', (int) $this->attachRow, true);

		return parent::toArray();
	}

	public function getAllowedMethods()
	{
		return array_merge(
			parent::getAllowedMethods(),
			[
				Mesour\Editable\Structures\PermissionsChecker::ATTACH,
			]
		);
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::MANY_TO_MANY;
	}

}
