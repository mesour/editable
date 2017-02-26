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
class EnumField extends BaseField implements Mesour\Editable\Rules\IValidated
{

	use Mesour\Sources\Structures\Nullable;

	private $values = [];

	public function setValues(array $values)
	{
		$this->values = [];
		foreach ($values as $key => $value) {
			$this->values[$key] = [
				'key' => $key,
				'name' => $value ? $value : $key,
			];
		}
		return $this;
	}

	public function addValue($key, $name = null)
	{
		$this->values[$key] = [
			'key' => $key,
			'name' => $name ? $name : $key,
		];
		return $this;
	}

	public function getValues()
	{
		return $this->values;
	}

	public function toArray()
	{
		$out = parent::toArray();

		//todo: translate value names

		$out['values'] = $this->values;
		$out['nullable'] = $this->isNullable();

		return $out;
	}

	/**
	 * @param string $value
	 * @return void
	 * @throws Mesour\Editable\ValidatorException
	 */
	public function validate($value)
	{
		if (!$this->isNullable() && ($value === null || $value === '')) {
			$exception = new Mesour\Editable\ValidatorException(Mesour\Editable\Rules\RuleHelper::$defaultText);
			$exception->setFieldName($this->getName());
			throw $exception;
		}
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::ENUM;
	}

}
