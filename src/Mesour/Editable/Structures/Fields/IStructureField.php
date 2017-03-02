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
interface IStructureField
{

	/**
	 * @param bool $disabled
	 */
	public function setDisabled($disabled = true);

	/**
	 * @return bool
	 */
	public function isDisabled();

	/**
	 * @param bool $inline
	 * @return mixed
	 */
	public function setInline($inline = true);

	/**
	 * @return bool
	 */
	public function isInline();

	/**
	 * @param string $role
	 * @param Mesour\Components\Security\IAuthorizator $authorizator
	 * @return bool
	 */
	public function isAllowedEdit($role, Mesour\Components\Security\IAuthorizator $authorizator);

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return mixed
	 */
	public function setEditPermission($resource, $privilege);

	public function getName();

	public function getType();

	/**
	 * @param mixed $identifier
	 * @return static
	 */
	public function addIdentifier($identifier);

	/**
	 * @return bool
	 */
	public function hasIdentifiers();

	public function setParameter($key, $value, $persistent = false);

	public function getParameter($key, $default = null);

	public function setTitle($title);

	/**
	 * @return array
	 */
	public function getAllowedMethods();

	/**
	 * @return array
	 */
	public function toArray();

}
