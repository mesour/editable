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
 *
 * @method void setReferenceRequired()
 * @method bool isReferenceRequired()
 */
trait CustomData
{

	/**
	 * @var array
	 */
	private $customData = [];

	public function useCustomData(array $customData = [])
	{
		$this->setReferenceRequired(false);
		$this->customData = $customData;
	}

	public function isUsedCustomData()
	{
		return !$this->isReferenceRequired();
	}

	/**
	 * @return array
	 */
	public function getCustomData()
	{
		return $this->customData;
	}

}
