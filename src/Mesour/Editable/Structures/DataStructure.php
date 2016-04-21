<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2016 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\Editable\Structures;

use Mesour;
use Mesour\Sources\Structures\Columns\IColumnStructure;
use Mesour\Sources\Structures\Columns\ManyToOneColumnStructure;
use Mesour\Sources\Structures\Columns\OneToOneColumnStructure;

/**
 * @author Matouš Němec (http://mesour.com)
 */
class DataStructure extends DataElementStructure implements IDataStructure
{

	/**
	 * @var IDataElementStructure[]
	 */
	private $elements = [];

	/**
	 * @var Mesour\Sources\ISource
	 */
	private $source;

	public function __construct($tableName = null, $primaryKey = null)
	{
		if (!$tableName) {
			throw new Mesour\InvalidArgumentException('First argument table name is required.');
		}
		if (!$primaryKey) {
			throw new Mesour\InvalidArgumentException('Second argument primary key is required.');
		}
		parent::__construct($tableName, $primaryKey);
	}

	public static function fromSource(Mesour\Sources\ISource $source)
	{
		$dataStrucute = self::determineStructureFromSource($source);

		$dataStrucute->setSource($source);
		return $dataStrucute;
	}

	public function setSource(Mesour\Sources\ISource $source)
	{
		$this->source = $source;
		return $this;
	}

	public function getSource($need = true)
	{
		if ($need && !$this->source) {
			throw new Mesour\InvalidStateException('Source is not set.');
		}
		return $this->source;
	}

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\OneToOneField
	 */
	public function addOneToOne($name, $title = null, $identifier = null)
	{
		return $this->createField(Fields\OneToOneField::class, $name, $title, $identifier);
	}

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\ManyToOneField
	 */
	public function addManyToOne($name, $title = null, $identifier = null)
	{
		return $this->createField(Fields\ManyToOneField::class, $name, $title, $identifier);
	}

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null $identifier
	 * @return Fields\OneToManyField
	 */
	public function addOneToMany($name, $title = null, $identifier = null)
	{
		return $this->createField(Fields\OneToManyField::class, $name, $title, $identifier);
	}

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null $identifier
	 * @return Fields\ManyToManyField
	 */
	public function addManyToMany($name, $title = null, $identifier = null)
	{
		return $this->createField(Fields\ManyToManyField::class, $name, $title, $identifier);
	}

	public function hasElement($tableName)
	{
		return isset($this->elements[$tableName]);
	}

	public function getElements()
	{
		return $this->elements;
	}

	public function getElement($tableName)
	{
		if (!isset($this->elements[$tableName])) {
			throw new Mesour\Sources\InvalidArgumentException(
				sprintf('Element %s not exist.', $tableName)
			);
		}
		return $this->elements[$tableName];
	}

	/**
	 * @param string $tableName
	 * @param string $primaryKey
	 * @return IDataElementStructure
	 */
	public function getOrCreateElement($tableName, $primaryKey)
	{
		if (!isset($this->elements[$tableName])) {
			$this->elements[$tableName] = $dataStructure = new DataElementStructure($tableName, $primaryKey);
			$dataStructure->setInInitializing($this->inInitializing);
		}
		return $this->getElement($tableName);
	}

	public function toArray()
	{
		$out = parent::toArray();

		if (!$this->source) {
			throw new Mesour\InvalidStateException(
				sprintf('Source is required if use %s.', static::class)
			);
		}

		foreach ($this->elements as $element) {
			$out['elements'][$element->getTableName()] = $element->toArray();
		}

		return $out;
	}

	/**
	 * @param bool $in
	 * @internal
	 */
	public function setInInitializing($in = true)
	{
		parent::setInInitializing($in);

		foreach ($this->elements as $element) {
			$element->setInInitializing($in);
		}
	}

	/**
	 * @param Mesour\Sources\ISource $source
	 * @return static
	 */
	protected static function determineStructureFromSource(Mesour\Sources\ISource $source)
	{
		$dataStrucute = new static($source->getTableName(), $source->getPrimaryKey());
		$dataStrucute->setInInitializing(true);
		foreach ($source->getDataStructure()->getTableStructures() as $tableStructure) {
			$elementStructure = $dataStrucute->getOrCreateElement(
				$tableStructure->getName(),
				$tableStructure->getPrimaryKey()
			);
			foreach ($tableStructure->getColumns() as $column) {
				self::detectBaseTypes($column->getType(), $elementStructure, $column);
			}
		}
		foreach ($source->getDataStructure()->getColumns() as $column) {
			$type = $column->getType();
			$isManyToOne = $type === IColumnStructure::MANY_TO_ONE;
			if ($isManyToOne || $type === IColumnStructure::ONE_TO_ONE) {
				/** @var OneToOneColumnStructure|ManyToOneColumnStructure $column */
				/** @var Fields\OneToOneField $field */
				if ($isManyToOne) {
					$field = $dataStrucute->addManyToOne($column->getName(), null, 0);
				} else {
					$field = $dataStrucute->addOneToOne($column->getName(), null, 0);
				}

				$field->setNullable($column->isNullable());
				$tableStrucure = $column->getTableStructure();
				$field->setReference(
					$tableStrucure->getName(),
					$tableStrucure->getPrimaryKey(),
					$column->getReferencedColumn(),
					$column->getPattern()
				);
			} elseif ($type === IColumnStructure::ONE_TO_MANY) {
				/** @var Mesour\Sources\Structures\Columns\OneToManyColumnStructure $column */
				/** @var Fields\OneToManyField $field */
				$field = $dataStrucute->addOneToMany($column->getName(), null, 0);
				$tableStrucure = $column->getTableStructure();
				$field->setReference(
					$tableStrucure->getName(),
					$tableStrucure->getPrimaryKey(),
					$column->getReferencedColumn(),
					$column->getPattern()
				);
			} elseif ($type === IColumnStructure::MANY_TO_MANY) {
				/** @var Mesour\Sources\Structures\Columns\ManyToManyColumnStructure $column */
				/** @var Fields\ManyToManyField $field */
				$field = $dataStrucute->addManyToMany($column->getName(), null, 0);
				$tableStrucure = $column->getTableStructure();
				$field->setReference(
					$tableStrucure->getName(),
					$tableStrucure->getPrimaryKey(),
					$column->getReferencedColumn(),
					$column->getPattern(),
					$column->getSelfColumn(),
					$column->getReferencedTable()
				);
			} else {
				self::detectBaseTypes($type, $dataStrucute, $column);
			}
		}
		$dataStrucute->setInInitializing(false);
		return $dataStrucute;
	}

	protected static function detectBaseTypes($type, IDataElementStructure $elementStructure, IColumnStructure $column)
	{
		if ($type === IColumnStructure::TEXT) {
			$field = $elementStructure->addText($column->getName(), null, 0);
		} elseif ($type === IColumnStructure::NUMBER) {
			$field = $elementStructure->addNumber($column->getName(), null, 0);
		} elseif ($type === IColumnStructure::DATE) {
			$field = $elementStructure->addDate($column->getName(), null, 0);
		} elseif ($type === IColumnStructure::ENUM) {
			/** @var Mesour\Sources\Structures\Columns\EnumColumnStructure $column */
			$field = $elementStructure->addEnum($column->getName(), null, 0);

			foreach ($column->getValues() as $value) {
				$field->addValue($value);
			}
		} elseif ($type === IColumnStructure::BOOL) {
			$field = $elementStructure->addBool($column->getName(), null, 0);
		} else {
			throw new Mesour\InvalidArgumentException(sprintf('Type %s is not recognized.', $type));
		}

		if (method_exists($field, 'setNullable')) {
			if (!method_exists($column, 'isNullable')) {
				throw new Mesour\InvalidStateException(
					sprintf('Method isNullable missing on column `%s`.', $column->getName())
				);
			}
			$field->setNullable($column->isNullable());
		}
	}

}
