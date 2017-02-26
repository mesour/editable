<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2017 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\Editable\Structures\Fields;

use Mesour\Editable\Rules\Rule;
use Mesour\Editable\Rules\RuleType;
use Mesour\Editable\ValidatorException;

/**
 * @author Matouš Němec (http://mesour.com)
 *
 * @method string getName()
 */
abstract class ValidatedField extends BaseField implements IValidatedField
{

	/**
	 * @var Rule[]
	 */
	private $rules = [];

	/**
	 * @param string|null $message
	 * @return static
	 */
	public function setRequired($message = null)
	{
		$this->addRule(RuleType::FILLED, $message);
		return $this;
	}

	/**
	 * @param string $type
	 * @param string|null $message
	 * @param mixed|null $arg
	 * @return static
	 */
	public function addRule($type, $message = null, $arg = null)
	{
		if ($type === RuleType::MIN) {
			$type = RuleType::RANGE;
			$arg = [$arg, null];
		} elseif ($type === RuleType::MAX) {
			$type = RuleType::RANGE;
			$arg = [null, $arg];
		}
		$this->rules[] = new Rule($type, $message, $arg);
		return $this;
	}

	public function validate($value)
	{
		foreach ($this->rules as $rule) {
			if (!$rule->isValid($value, method_exists($this, 'isNullable') && $this->isNullable())) {
				$exception = new ValidatorException($rule->getMessage());
				$exception->setFieldName($this->getName());
				throw $exception;
			}
		}
		return true;
	}

	/**
	 * @return Rule[]
	 */
	public function getRules()
	{
		return $this->rules;
	}

}
