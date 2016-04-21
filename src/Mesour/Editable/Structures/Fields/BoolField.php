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
class BoolField extends BaseField
{

	use Mesour\Sources\Structures\Nullable;

	private $description;

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::BOOL;
	}

	public function toArray()
	{
		$out = parent::toArray();

		$out['nullable'] = $this->isNullable();

		//todo: translate description
		$out['description'] = $this->description ?: $this->getName();

		return $out;
	}

}
