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

/**
 * @author Matouš Němec (http://mesour.com)
 */
interface IDataStructure extends IDataElementStructure
{

	public function setSource(Mesour\Sources\ISource $source);

	/**
	 * @param bool $need
	 * @return Mesour\Sources\ISource
	 */
	public function getSource($need = true);

	public static function fromSource(Mesour\Sources\ISource $source);

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null|mixed $identifier
	 * @return Fields\OneToOneField
	 */
	public function addOneToOne($name, $title = null, $identifier = null);

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null $identifier
	 * @return Fields\OneToManyField
	 */
	public function addOneToMany($name, $title = null, $identifier = null);

	/**
	 * @param string $name
	 * @param null|string $title
	 * @param null $identifier
	 * @return Fields\ManyToManyField
	 */
	public function addManyToMany($name, $title = null, $identifier = null);

	/**
	 * @param string $tableName
	 * @return bool
	 */
	public function hasElement($tableName);

	/**
	 * @return IDataElementStructure[]
	 */
	public function getElements();

	/**
	 * @param string $tableName
	 * @return IDataElementStructure
	 */
	public function getElement($tableName);

	/**
	 * @param string $tableName
	 * @param string $primaryKey
	 * @return IDataElementStructure
	 */
	public function getOrCreateElement($tableName, $primaryKey);

}
