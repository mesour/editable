<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2017 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\Editable;

use Mesour;

/**
 * @author Matouš Němec (http://mesour.com)
 */
class ValidatorException extends \Exception
{

	/**
	 * @var string
	 */
	private $fieldName;

	/**
	 * @return string
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}

	/**
	 * @param string $fieldName
	 */
	public function setFieldName($fieldName)
	{
		$this->fieldName = $fieldName;
	}

}

class InvalidDateTimeException extends ValidatorException
{

}
