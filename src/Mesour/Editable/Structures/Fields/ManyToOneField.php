<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2017 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\Editable\Structures\Fields;

use Mesour;

/**
 * @author Matouš Němec (http://mesour.com)
 */
class ManyToOneField extends BaseElementField
{

	use Mesour\Sources\Structures\Nullable;

	private $editCurrentRow = false;

	public function toArray()
	{
		$this->setParameter('edit_current_row', $this->editCurrentRow, true);

		$out = parent::toArray();

		$out['nullable'] = $this->isNullable();

		return $out;
	}

	public function getType()
	{
		return Mesour\Sources\Structures\Columns\IColumnStructure::MANY_TO_ONE;
	}

	public function enableRemoveRow()
	{
		throw new Mesour\NotImplementedException('Set nullable to true instead.');
	}

	public function disableRemoveRow()
	{
		throw new Mesour\NotImplementedException('Set nullable to false instead.');
	}

	public function hasRemoveRowEnabled()
	{
		return true;
	}

	public function enableEditCurrentRow()
	{
		$this->editCurrentRow = true;
		return $this;
	}

	public function disableEditCurrentRow()
	{
		$this->editCurrentRow = false;
		return $this;
	}

	public function hasEditCurrentRowEnabled()
	{
		return $this->editCurrentRow;
	}

}
