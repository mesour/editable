<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2016 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\Editable\Structures\Fields;

use Mesour;

/**
 * @author Matouš Němec (http://mesour.com)
 */
class ManyToManyField extends BaseElementField
{

	public function __construct($name)
	{
		parent::__construct($name);

		$this->enableCreateNewRow();
	}

	public function setReference($table, $primaryKey, $referencedColumn, $pattern = null, $selfColumn = null, $referencedTable = null)
	{
		parent::setReference($table, $primaryKey, $referencedColumn, $pattern);
		$this->reference['self_column'] = $selfColumn;
		$this->reference['referenced_table'] = $referencedTable;
		return $this;
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::MANY_TO_MANY;
	}

}
