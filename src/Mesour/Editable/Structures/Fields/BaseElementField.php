<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2017 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\Editable\Structures\Fields;

use Mesour;

/**
 * @author Matouš Němec (http://mesour.com)
 */
abstract class BaseElementField extends BaseField implements IStructureElementField
{

	protected $reference;

	protected $referenceRequired = true;

	private $createNewRow = false;

	private $removeRow = false;

	private $createPermission;

	private $removePermission;

	/**
	 * @param bool $referenceRequired
	 */
	public function setReferenceRequired($referenceRequired)
	{
		$this->referenceRequired = $referenceRequired;
	}

	/**
	 * @return bool
	 */
	public function isReferenceRequired()
	{
		return $this->referenceRequired;
	}

	public function enableCreateNewRow()
	{
		$this->createNewRow = true;
		return $this;
	}

	public function disableCreateNewRow()
	{
		$this->createNewRow = false;
		return $this;
	}

	public function hasCreateNewRowEnabled()
	{
		return $this->createNewRow;
	}

	public function enableRemoveRow()
	{
		$this->removeRow = true;
		return $this;
	}

	public function disableRemoveRow()
	{
		$this->removeRow = false;
		return $this;
	}

	public function hasRemoveRowEnabled()
	{
		return $this->removeRow;
	}

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return $this
	 */
	public function setCreatePermission($resource, $privilege)
	{
		$this->createPermission = [$resource, $privilege];
		return $this;
	}

	/**
	 * @param mixed|array $roles
	 * @param Mesour\Components\Security\IAuthorizator $authorizator
	 * @return bool
	 */
	public function isAllowedCreate($roles, Mesour\Components\Security\IAuthorizator $authorizator)
	{
		return $this->checkIsAllowed($this->createPermission, $roles, $authorizator);
	}

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return $this
	 */
	public function setRemovePermission($resource, $privilege)
	{
		$this->removePermission = [$resource, $privilege];
		return $this;
	}

	/**
	 * @param string|array $roles
	 * @param Mesour\Components\Security\IAuthorizator $authorizator
	 * @return bool
	 */
	public function isAllowedRemove($roles, Mesour\Components\Security\IAuthorizator $authorizator)
	{
		return $this->checkIsAllowed($this->removePermission, $roles, $authorizator);
	}

	public function getReference()
	{
		return $this->reference;
	}

	public function setReference($table, $primaryKey, $referencedColumn, $pattern = null)
	{
		$this->reference = [
			'table' => $table,
			'primary_key' => $primaryKey,
			'column' => $referencedColumn,
		];
		if ($pattern) {
			$this->reference['pattern'] = $pattern;
		}
		return $this;
	}

	public function toArray()
	{
		$this->setParameter('create_new_row', $this->createNewRow, true);
		$this->setParameter('remove_row', $this->removeRow, true);

		$out = parent::toArray();

		if ($this->referenceRequired) {
			if (!$this->reference) {
				throw new Mesour\InvalidStateException(
					sprintf(
						"Element field require reference. Is registered relational column '%s' on source data structure?",
						$this->getName()
					)
				);
			}
			$out['reference'] = $this->reference;
		} else {
			$out['reference'] = [
				'table' => isset($this->reference['table']) ? $this->reference['table'] : null,
				'primary_key' => isset($this->reference['primary_key']) ? $this->reference['primary_key'] : null,
				'column' => isset($this->reference['column']) ? $this->reference['column'] : null,
			];
		}

		return $out;
	}

	public function getAllowedMethods()
	{
		return array_merge(
			parent::getAllowedMethods(),
			[
				Mesour\Editable\Structures\PermissionsChecker::CREATE,
				Mesour\Editable\Structures\PermissionsChecker::REMOVE,
				Mesour\Editable\Structures\PermissionsChecker::EDIT_FORM,
			]
		);
	}

	public function getType()
	{
		return 'one_to_one';
	}

}
