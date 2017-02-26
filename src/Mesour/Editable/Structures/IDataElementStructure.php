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
interface IDataElementStructure
{

	/**
	 * @return string|null
	 */
	public function getTableName();

	/**
	 * @return string|null
	 */
	public function getPrimaryKey();

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null $identifier
	 * @return Fields\TextField
	 */
	public function addText($name, $title = null, $identifier = null);

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\NumberField
	 */
	public function addNumber($name, $title = null, $identifier = null);

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\DateField
	 */
	public function addDate($name, $title = null, $identifier = null);

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\EnumField
	 */
	public function addEnum($name, $title = null, $identifier = null);

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\BoolField
	 */
	public function addBool($name, $title = null, $identifier = null);

	/**
	 * @return Mesour\Editable\Structures\Fields\IStructureField[]
	 */
	public function getFields();

	/**
	 * @param string $name
	 * @return Mesour\Editable\Structures\Fields\IStructureField|Mesour\Editable\Structures\Fields\IStructureElementField
	 */
	public function getField($name);

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasField($name);

	/**
	 * @param string $name
	 * @return void
	 */
	public function removeField($name);

	/**
	 * @return array
	 */
	public function toArray();

	/**
	 * @param bool $in
	 * @internal
	 */
	public function setInInitializing($in = true);

}
