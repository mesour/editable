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
abstract class BaseField implements IStructureField
{

	private $name;

	private $title;

	private $disabled = false;

	private $parameters = [];

	private $identifiers = [];

	private $editPermission;

	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * @param bool $disabled
	 * @return $this
	 */
	public function setDisabled($disabled = true)
	{
		$this->disabled = (bool) $disabled;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->disabled;
	}

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return $this
	 */
	public function setEditPermission($resource, $privilege)
	{
		$this->editPermission = [$resource, $privilege];
		return $this;
	}

	/**
	 * @param string|array $roles
	 * @param Mesour\Components\Security\IAuthorizator $authorizator
	 * @return bool
	 */
	public function isAllowedEdit($roles, Mesour\Components\Security\IAuthorizator $authorizator)
	{
		return $this->checkIsAllowed($this->editPermission, $roles, $authorizator);
	}

	/**
	 * @param array|null $permission
	 * @param string|array $roles
	 * @param Mesour\Components\Security\IAuthorizator $authorizator
	 * @return bool
	 */
	protected function checkIsAllowed($permission, $roles, Mesour\Components\Security\IAuthorizator $authorizator)
	{
		return !$permission || Mesour\Components\Utils\Helpers::invokeArgs(
			[$authorizator, 'isAllowed'],
			array_merge($roles, $permission)
		);
	}

	public function getName()
	{
		return $this->name;
	}

	public function setTitle($title)
	{
		if ($title) {
			$this->title = $title;
		}
		return $this;
	}

	public function addIdentifier($identifier)
	{
		$this->identifiers[] = $identifier;
		return $this;
	}

	public function setParameter($key, $value, $persistent = false)
	{
		if ($this->hasIdentifiers()) {
			if ($persistent) {
				foreach ($this->parameters as $identifier => $val) {
					$this->parameters[$identifier][$key] = $value;
				}
			}
			$lastIdentifier = end($this->identifiers);
			$this->parameters[$lastIdentifier][$key] = $value;
		} else {
			$this->parameters[$key] = $value;
		}
		return $this;
	}

	public function getParameter($key, $default = null)
	{
		return !isset($this->parameters[$key]) ? $default : $this->parameters[$key];
	}

	public function hasIdentifiers()
	{
		return count($this->identifiers) > 0;
	}

	public function toArray()
	{
		$out = [
			'name' => $this->getName(),
			'title' => !$this->title ? $this->getName() : $this->title,
			'type' => $this->getType(),
			'params' => $this->parameters,
		];

		if ($this instanceof IValidatedField) {
			$out['rules'] = $this->getRules();
		}
		return $out;
	}

	public function getAllowedMethods()
	{
		return [Mesour\Editable\Structures\PermissionsChecker::EDIT];
	}

	abstract public function getType();

}
