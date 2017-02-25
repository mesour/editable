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
interface IValidatedField extends IStructureField
{

	/**
	 * @param string $value
	 * @return void
	 * @throws Mesour\Editable\ValidatorException
	 */
	public function validate($value);

	/**
	 * @param string $type
	 * @param string|null $message
	 * @return void
	 */
	public function addRule($type, $message = null);

	/**
	 * @return Mesour\Editable\Rules\Rule[]
	 */
	public function getRules();

}
