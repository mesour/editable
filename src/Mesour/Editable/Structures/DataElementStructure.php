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
class DataElementStructure implements IDataElementStructure
{

	private $fields = [];

	private $tableName;

	private $primaryKey;

	protected $inInitializing = false;

	public function __construct($tableName = null, $primaryKey = null)
	{
		$this->tableName = $tableName;
		$this->primaryKey = $primaryKey;
	}

	/**
	 * @return string|null
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * @return string|null
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\TextField
	 */
	public function addText($name, $title = null, $identifier = null)
	{
		return $this->createField(Fields\TextField::class, $name, $title, $identifier);
	}

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\NumberField
	 */
	public function addNumber($name, $title = null, $identifier = null)
	{
		return $this->createField(Fields\NumberField::class, $name, $title, $identifier);
	}

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\DateField
	 */
	public function addDate($name, $title = null, $identifier = null)
	{
		return $this->createField(Fields\DateField::class, $name, $title, $identifier);
	}

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\EnumField
	 */
	public function addEnum($name, $title = null, $identifier = null)
	{
		return $this->createField(Fields\EnumField::class, $name, $title, $identifier);
	}

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\BoolField
	 */
	public function addBool($name, $title = null, $identifier = null)
	{
		return $this->createField(Fields\BoolField::class, $name, $title, $identifier);
	}

	/**
	 * @param string $type Custom type
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\CustomField
	 */
	public function addCustom($type, $name, $title = null, $identifier = null)
	{
		/** @var Fields\CustomField $field */
		$field = $this->createField(Fields\CustomField::class, $name, $title, $identifier);
		$field->setCustomType($type);
		return $field;
	}

	/**
	 * @return Fields\IStructureField[]
	 */
	public function getFields()
	{
		return $this->fields;
	}

	public function getField($name)
	{
		if (!$this->hasField($name)) {
			throw new Mesour\InvalidArgumentException(
				sprintf('Field with name %s not exits.', $name)
			);
		}
		return $this->fields[$name];
	}

	public function toArray()
	{
		$fields = [];
		foreach ($this->getFields() as $field) {
			if (!$field->isDisabled()) {
				$fields[] = $field->toArray();
			}
		}
		return ['fields' => $fields];
	}

	/**
	 * @param bool $in
	 * @internal
	 */
	public function setInInitializing($in = true)
	{
		$this->inInitializing = $in;
	}

	public function hasField($name)
	{
		return isset($this->fields[$name]);
	}

	public function removeField($name)
	{
		unset($this->fields[$name]);
		return $this;
	}

	protected function createField($class, $name, $title, $identifier)
	{
		/** @var Fields\IStructureField $field */
		if ($this->hasField($name)) {
			$field = $this->getField($name);
		} else {
			$field = new $class($name);
			$this->addField($field);
		}

		$identifier = $identifier ?: 0;
		$field->addIdentifier($identifier);
		$field->setParameter('id', $identifier);
		$field->setTitle($title);
		if (!$this->inInitializing) {
			$field->setDisabled(false);
		} else {
			$field->setDisabled(true);
		}
		return $field;
	}

	private function addField(Fields\IStructureField $field)
	{
		$this->fields[$field->getName()] = $field;
	}

}
