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
interface IStructureElementField extends IStructureField
{

	public function enableCreateNewRow();

	public function disableCreateNewRow();

	public function hasCreateNewRowEnabled();

	public function enableRemoveRow();

	public function disableRemoveRow();

	public function hasRemoveRowEnabled();

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return static
	 */
	public function setCreatePermission($resource, $privilege);

	/**
	 * @param mixed $role
	 * @param Mesour\Components\Security\IAuthorizator $authorizator
	 * @return bool
	 */
	public function isAllowedCreate($role, Mesour\Components\Security\IAuthorizator $authorizator);

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return static
	 */
	public function setRemovePermission($resource, $privilege);

	/**
	 * @param string $role
	 * @param Mesour\Components\Security\IAuthorizator $authorizator
	 * @return bool
	 */
	public function isAllowedRemove($role, Mesour\Components\Security\IAuthorizator $authorizator);

	public function setReference($table, $primaryKey, $referencedColumn, $pattern = null);

	public function getReference();

}
