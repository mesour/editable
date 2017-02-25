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
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * @author Matouš Němec (http://mesour.com)
 */
class Rule implements \JsonSerializable
{

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var mixed|null
	 */
	private $arg;

	/**
	 * @param string $type
	 * @param string|null $message
	 * @param mixed|null $arg
	 */
	public function __construct($type, $message = null, $arg = null)
	{
		if (!RuleType::isValid($type)) {
			throw new Mesour\InvalidArgumentException(sprintf('Type %s is not valid rule type.', $type));
		}

		$this->type = $type;
		$this->message = $message ?: RuleHelper::getErrorText($type);
		$this->arg = $arg;
	}

	public function isValid($value)
	{
		$valid = true;
		if ($this->type === RuleType::FILLED) {
			$valid = Strings::length((string) $value) > 0;
		} elseif ($this->type === RuleType::FLOAT) {
			$valid = Validators::is($value, 'float');
		} elseif ($this->type === RuleType::PATTERN) {
			$valid = Validators::is($value, 'pattern:' . $this->arg);
		} elseif ($this->type === RuleType::EQUAL) {
			$valid = $value === $this->arg;
		} elseif ($this->type === RuleType::NOT_EQUAL) {
			$valid = $value !== $this->arg;
		} elseif ($this->type === RuleType::MIN_LENGTH) {
			$valid = Strings::length((string) $value) >= (int) $this->arg;
		} elseif ($this->type === RuleType::LENGTH) {
			$valid = Strings::length((string) $value) === (int) $this->arg;
		} elseif ($this->type === RuleType::MAX_LENGTH) {
			$valid = Strings::length((string) $value) <= (int) $this->arg;
		} elseif (RuleHelper::hasValidator($this->type)) {
			$valid = RuleHelper::validate($this->type, $value, $this->arg);
		}
		return $valid;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	public function jsonSerialize()
	{
		return [
			'type' => $this->type,
			'message' => $this->message,
			'arg' => $this->arg,
		];
	}

}
