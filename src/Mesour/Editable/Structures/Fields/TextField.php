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
class TextField extends BaseField
{

	use Mesour\Sources\Structures\Nullable;

	private $hasTextarea = false;

	public function setTextarea($textarea = true)
	{
		$this->hasTextarea = $textarea;
		return $this;
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::TEXT;
	}

	public function toArray()
	{
		$out = parent::toArray();

		$out['nullable'] = $this->isNullable();
		$out['hasTextarea'] = !$this->hasTextarea ? 'false' : 'true';

		return $out;
	}

}
