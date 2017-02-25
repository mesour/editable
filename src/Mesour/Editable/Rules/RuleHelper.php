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
class RuleHelper
{

	public static $validators = [
		RuleType::EMAIL => 'isEmail',
		RuleType::URL => 'isUrl',
		RuleType::NUMERIC => 'isNumeric',
		RuleType::RANGE => 'isInRange',
		RuleType::INTEGER => 'isNumericInt',
	];

	public static $errorTexts = [
		RuleType::PATTERN => 'Value is not valid by pattern.',
		RuleType::EMAIL => 'Value must be valid email address.',
		RuleType::URL => 'Value must be valid url address.',
		RuleType::NUMERIC => 'Value must be valid number.',
		RuleType::RANGE => 'Value is not in valid range.',
		RuleType::MIN => 'Value is not at the required minimum.',
		RuleType::MAX => 'Value is not at the required maxumum.',
		RuleType::FLOAT => 'Value must be float.',
		RuleType::INTEGER => 'Value must be integer.',
		RuleType::MIN_LENGTH => 'Value must be in required minimum length.',
		RuleType::MAX_LENGTH => 'Value must be in required maximum length.',
		RuleType::LENGTH => 'Value must be in required length.',
		RuleType::EQUAL => 'Value must be equal.',
		RuleType::NOT_EQUAL => 'Value must not be equal.',
	];

	public static $defaultText = 'Field is required.';

	/**
	 * @param string $type
	 * @param mixed $value
	 * @param mixed|null $arg
	 * @return mixed
	 */
	public static function validate($type, $value, $arg = null)
	{
		if (!static::hasValidator($type)) {
			throw new Mesour\InvalidArgumentException(sprintf('Validator %s not exist.', $type));
		}
		return call_user_func('Nette\Utils\Validators::' . static::$validators[$type], $value, $arg);
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public static function hasValidator($type)
	{
		return isset(static::$validators[$type]);
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public static function getErrorText($type)
	{
		return isset(static::$errorTexts[$type]) ? static::$errorTexts[$type] : static::$defaultText;
	}

}
