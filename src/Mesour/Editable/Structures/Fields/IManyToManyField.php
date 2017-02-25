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
interface IManyToManyField extends IStructureElementField
{

	public function enableAttachRow();

	public function disableAttachRow();

	public function hasAttachRowEnabled();

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return static
	 */
	public function setAttachPermission($resource, $privilege);

	/**
	 * @param string $role
	 * @param Mesour\Components\Security\IAuthorizator $authorizator
	 * @return bool
	 */
	public function isAllowedAttach($role, Mesour\Components\Security\IAuthorizator $authorizator);

}
