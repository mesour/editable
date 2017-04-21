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
class CustomField extends BaseField
{

	const TYPE_CUSTOM = 'custom';

	use Mesour\Sources\Structures\Nullable;

	/**
	 * @var string
	 */
	private $customType;

	/**
	 * @param string $customType
	 * @return $this
	 */
	public function setCustomType($customType)
	{
		$this->customType = $customType;
		return $this;
	}

	public function getType()
	{
		return static::TYPE_CUSTOM;
	}

	public function toArray()
	{
		$out = parent::toArray();

		$out['nullable'] = $this->isNullable();

		$out['customType'] = $this->customType;

		return $out;
	}

}
