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
class DateField extends BaseField
{

	private $format = 'Y-m-d';

	public function setFormat($format)
	{
		$this->format = $format;
		return $this;
	}

	public function toArray()
	{
		$out = parent::toArray();

		$out['format'] = Mesour\Components\Utils\Helpers::convertDateToJsFormat($this->format);

		return $out;
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::DATE;
	}

}
