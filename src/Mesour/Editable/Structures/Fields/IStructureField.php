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
	public function toArray();

}
