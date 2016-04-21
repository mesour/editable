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
use Mesour\Editable\Structures\Fields\IStructureField;
use Mesour\Editable\Structures\Fields\IStructureElementField;
use Mesour\Editable\Structures\Fields\IManyToManyField;

/**
 * @author Matouš Němec (http://mesour.com)
 */
class PermissionsChecker
{

	const EDIT = 'edit';

	const EDIT_FORM = 'edit_form';

	const CREATE = 'create';

	const REMOVE = 'remove';

	const ATTACH = 'attach';

	private static $methods = [
		self::EDIT,
		self::EDIT_FORM,
		self::CREATE,
		self::REMOVE,
		self::ATTACH,
	];

	private static $permissionError = 'You have not permission for this action.';

	public static function check($method, Mesour\UI\Editable $editable, IStructureField $field)
	{
		if (!self::hasMethod($method)) {
			throw new Mesour\InvalidArgumentException(sprintf('Method `%s` does not exist.', $method));
		}

		if ($method === self::EDIT || $method === self::EDIT_FORM) {
			self::checkEdit($method, $editable, $field);
		} elseif ($method === self::CREATE) {
			self::checkCreate($editable, $field);
		} elseif ($method === self::REMOVE) {
			self::checkRemove($editable, $field);
		} elseif ($method === self::ATTACH) {
			self::checkAttach($editable, $field);
		}
	}

	public static function hasMethod($method)
	{
		return in_array($method, self::$methods, true);
	}

	private static function checkAttach(Mesour\UI\Editable $editable, IStructureField $field)
	{
		self::checkDisabled($field);

		self::checkIsMethodAllowed($field, self::ATTACH);

		if (!$field instanceof IManyToManyField) {
			throw new Mesour\InvalidArgumentException(
				sprintf(
					'Field `%s` must be type %s for attaching existing row.',
					$field->getName(),
					IManyToManyField::class
				)
			);
		}
		if (!$field->hasAttachRowEnabled()) {
			throw new Mesour\InvalidStateException(
				sprintf('Field `%s` has not enabled attach existing row.', $field->getName())
			);
		}
		if (!$field->isAllowedAttach($editable->getUserRole(), $editable->getAuthorizator())) {
			throw new Mesour\Editable\ValidatorException(self::$permissionError);
		}
	}

	private static function checkCreate(Mesour\UI\Editable $editable, IStructureField $field)
	{
		$field = self::checkStructureElementField($field);

		self::checkDisabled($field);

		self::checkIsMethodAllowed($field, self::CREATE);

		if (!$field->hasCreateNewRowEnabled()) {
			throw new Mesour\InvalidStateException(
				sprintf('Field `%s` has not enabled create new row.', $field->getName())
			);
		}

		if (!$field->isAllowedCreate($editable->getUserRole(), $editable->getAuthorizator())) {
			throw new Mesour\Editable\ValidatorException(self::$permissionError);
		}
	}

	private static function checkRemove(Mesour\UI\Editable $editable, IStructureField $field)
	{
		$field = self::checkStructureElementField($field);

		self::checkDisabled($field);

		self::checkIsMethodAllowed($field, self::REMOVE);

		if (!$field->hasRemoveRowEnabled()) {
			throw new Mesour\InvalidStateException(
				sprintf('Field `%s` has not enabled remove row.', $field->getName())
			);
		}

		if (!$field->isAllowedRemove($editable->getUserRole(), $editable->getAuthorizator())) {
			throw new Mesour\Editable\ValidatorException(self::$permissionError);
		}
	}

	private static function checkEdit($method, Mesour\UI\Editable $editable, IStructureField $field)
	{
		self::checkDisabled($field);

		self::checkIsMethodAllowed($field, self::EDIT);

		if ($method === self::EDIT_FORM
			&& $field instanceof Mesour\Editable\Structures\Fields\ManyToOneField
			&& !$field->hasEditCurrentRowEnabled()
		) {
			throw new Mesour\InvalidArgumentException(
				sprintf('Field `%s` have not enabled edit current row.', $field->getName())
			);
		}

		if (!$field->isAllowedEdit($editable->getUserRole(), $editable->getAuthorizator())) {
			throw new Mesour\Editable\ValidatorException(self::$permissionError);
		}
	}

	/**
	 * @param IStructureField $field
	 * @return IStructureElementField
	 */
	private static function checkStructureElementField(IStructureField $field)
	{
		if (!$field instanceof IStructureElementField) {
			throw new Mesour\InvalidArgumentException(
				sprintf(
					'Field `%s` must be type %s for creating new row.',
					$field->getName(),
					IStructureElementField::class
				)
			);
		}
		return $field;
	}

	private static function checkIsMethodAllowed(IStructureField $field, $method)
	{
		if (!in_array($method, $field->getAllowedMethods(), true)) {
			throw new Mesour\InvalidStateException(
				sprintf('Field `%s` does not have allowed method %s.', $field->getName(), $method)
			);
		}
	}

	private static function checkDisabled(IStructureField $field)
	{
		if ($field->isDisabled()) {
			throw new Mesour\InvalidStateException(
				sprintf('Can not change data for disabled field `%s`.', $field->getName())
			);
		}
	}

}
