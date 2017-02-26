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
class NumberField extends ValidatedField
{

	use Mesour\Sources\Structures\Nullable;

	private $unit = null;

	private $separator = ',';

	private $decimalPoint = '.';

	private $decimals = 0;

	public function __construct($name)
	{
		parent::__construct($name);
	}

	public function setUnit($unit)
	{
		$this->unit = $unit;
		return $this;
	}

	public function setThousandSeparator($separator)
	{
		$this->separator = $separator;
		return $this;
	}

	public function setDecimalPoint($decimalPoint)
	{
		$this->decimalPoint = $decimalPoint;
		return $this;
	}

	public function setDecimals($decimals)
	{
		$this->decimals = (int) $decimals;
		return $this;
	}

	public function getDecimals()
	{
		return $this->decimals;
	}

	public function toArray()
	{
		$out = parent::toArray();

		$out['unit'] = $this->unit;
		$out['separator'] = $this->separator;
		$out['decimalPoint'] = $this->decimalPoint;
		$out['decimals'] = $this->decimals;
		$out['nullable'] = $this->isNullable();

		return $out;
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::NUMBER;
	}

}
