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
class EnumField extends BaseField
{

	private $values = [];

	public function addValue($key, $name = null)
	{
		$this->values[$key] = [
			'key' => $key,
			'name' => $name ? $name : $key,
		];
		return $this;
	}

	public function toArray()
	{
		$out = parent::toArray();

		//todo: translate value names

		$out['values'] = $this->values;

		return $out;
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::ENUM;
	}

}
