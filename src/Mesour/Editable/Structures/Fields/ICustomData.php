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
interface ICustomData extends IStructureElementField
{

	public function useCustomData(array $customData = []);

	/**
	 * @return bool
	 */
	public function isUsedCustomData();

	/**
	 * @return array
	 */
	public function getCustomData();

}
