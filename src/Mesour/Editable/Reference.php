<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2017 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\Editable\Structures;

use Mesour;

/**
 * @author Matouš Němec (http://mesour.com)
 */
class Reference
{

	/**
	 * @var int
	 */
	private $fromId;

	/**
	 * @var null|string
	 */
	private $fromValue;

	/**
	 * @var int
	 */
	private $toId;

	/**
	 * @var null|string
	 */
	private $toValue;

	public function __construct($fromId, $toId, $fromValue = null, $toValue = null)
	{
		$this->fromId = $fromId;
		$this->toId = $toId;
		$this->fromValue = is_numeric($fromValue) ? (int) $fromValue : $fromValue;
		$this->toValue = is_numeric($toValue) ? (int) $toValue : $toValue;
	}

	/**
	 * @return int
	 */
	public function getFromId()
	{
		return $this->fromId;
	}

	/**
	 * @return null|string
	 */
	public function getFromValue()
	{
		return $this->fromValue;
	}

	/**
	 * @return int
	 */
	public function getToId()
	{
		return $this->toId;
	}

	/**
	 * @return null|string
	 */
	public function getToValue()
	{
		return $this->toValue;
	}

}
