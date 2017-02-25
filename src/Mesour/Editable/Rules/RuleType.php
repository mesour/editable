<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2017 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\Editable\Rules;

use Mesour;

/**
 * @author Matouš Němec (http://mesour.com)
 */
class RuleType
{

	const EMAIL = 'email';
	const FILLED = 'filled';
	const NUMERIC = 'numeric';
	const RANGE = 'range';
	const MIN = 'min';
	const MAX = 'max';
	const FLOAT = 'float';
	const MIN_LENGTH = 'minLength';
	const MAX_LENGTH = 'maxLength';
	const LENGTH = 'length';
	const EQUAL = 'equal';
	const NOT_EQUAL = 'notEqual';
	const INTEGER = 'integer';
	const PATTERN = 'pattern';
	const URL = 'url';

	/**
	 * @return array
	 */
	public static function getAll()
	{
		return [
			static::EMAIL,
			static::FILLED,
			static::NUMERIC,
			static::RANGE,
			static::MIN,
			static::MAX,
			static::FLOAT,
			static::MIN_LENGTH,
			static::MAX_LENGTH,
			static::LENGTH,
			static::EQUAL,
			static::NOT_EQUAL,
			static::INTEGER,
			static::PATTERN,
			static::URL,
		];
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public static function isValid($type)
	{
		return in_array($type, static::getAll(), true);
	}

}
