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
abstract class BaseElementField extends BaseField implements IStructureElementField
{

	protected $reference;

	private $createNewRow = false;

	public function enableCreateNewRow()
	{
		$this->createNewRow = true;
		return $this;
	}

	public function disableCreateNewRow()
	{
		$this->createNewRow = false;
		return $this;
	}

	public function getReference()
	{
		return $this->reference;
	}

	public function setReference($table, $primaryKey, $referencedColumn, $pattern = null)
	{
		$this->reference = [
			'table' => $table,
			'primary_key' => $primaryKey,
			'column' => $referencedColumn,
		];
		if ($pattern) {
			$this->reference['pattern'] = $pattern;
		}
		return $this;
	}

	public function toArray()
	{
		$this->setParameter('create_new_row', (int) $this->createNewRow, true);

		$out = parent::toArray();

		if (!$this->reference) {
			throw new Mesour\InvalidStateException(
				sprintf(
					"Element field require reference. Is registered relational column '%s' on source data structure?",
					$this->getName()
				)
			);
		}
		$out['reference'] = $this->reference;

		return $out;
	}

	public function getType()
	{
		return 'one_to_one';
	}

}
