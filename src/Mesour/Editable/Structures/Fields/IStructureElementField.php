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
interface IStructureElementField extends IStructureField
{

	public function enableCreateNewRow();

	public function disableCreateNewRow();

	public function setReference($table, $primaryKey, $referencedColumn, $pattern = null);

	public function getReference();

}
