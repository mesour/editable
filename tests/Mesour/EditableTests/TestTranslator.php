<?php

namespace Mesour\EditableTests;

use Mesour\Components\Localization\ITranslator;

class TestTranslator implements ITranslator
{

	private $translates = [];

	public function __construct(array $translates)
	{
		$this->translates = $translates;
	}

	public function translate($message, $count = null)
	{
		return isset($this->translates[$message]) ? $this->translates[$message] : $message;
	}

}
