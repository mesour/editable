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
class OneToManyField extends BaseElementField
{

	public function __construct($name)
	{
		parent::__construct($name);

		$this->enableCreateNewRow();
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::ONE_TO_MANY;
	}

}
