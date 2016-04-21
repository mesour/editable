<?php
/**
 * This file is part of the Mesour Editable (http://components.mesour.com/component/editable)
 *
 * Copyright (c) 2016 Matouš Němec (http://mesour.com)
 *
 * For full licence and copyright please view the file licence.md in root of this project
 */

namespace Mesour\UI;

use Mesour;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Mesour\Editable\Structures\PermissionsChecker;

/**
 * @author Matouš Němec (http://mesour.com)
 *
 * @method null onRender(Editable $editable)
 * @method null onCreate(\Mesour\Editable\Structures\Fields\IStructureElementField $field, array $newValues, $identifier = null, array $params = [])
 * @method null onRemove(\Mesour\Editable\Structures\Fields\IStructureElementField $field, $value, $identifier = null)
 * @method null onAttach(\Mesour\Editable\Structures\Fields\IStructureElementField $field, \Mesour\Editable\Structures\Reference $reference, $identifier = null, array $params = [])
 * @method null onEditField(\Mesour\Editable\Structures\Fields\IStructureField $field, $newValue, $oldValue = null, $identifier = null, array $params = [])
 * @method null onEditElement(\Mesour\Editable\Structures\Fields\IStructureElementField $field, array $values, array $oldValues, \Mesour\Editable\Structures\Reference $reference, $identifier = null, array $params = [])
 */
class Editable extends Mesour\Components\Control\AttributesControl
{

	const WRAPPER = 'wrapper';

	private $disabled = false;

	/**
	 * @var Mesour\Editable\Structures\IDataStructure
	 */
	private $dataStructure;

	private $inline = false;

	private $disabledInlineAlerts = false;

	public $onRender = [];

	public $onCreate = [];

	public $onRemove = [];

	public $onAttach = [];

	public $onEditField = [];

	public $onEditElement = [];

	protected $defaults = [
		self::WRAPPER => [
			'el' => 'div',
		],
	];

	public function __construct($name = null, Mesour\Components\ComponentModel\IContainer $parent = null)
	{
		if (is_null($name)) {
			throw new Mesour\InvalidStateException('Component name is required.');
		}
		parent::__construct($name, $parent);

		$this->setHtmlElement(
			Mesour\Components\Utils\Html::el($this->getOption(self::WRAPPER, 'el'))
		);

		$this->addComponent(new Modal('modal'));
	}

	/**
	 * @return bool
	 */
	public function isInline()
	{
		return $this->inline;
	}

	/**
	 * @param bool $inline
	 */
	public function setInline($inline = true)
	{
		$this->inline = $inline;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isDisabledInlineAlerts()
	{
		return $this->disabledInlineAlerts;
	}

	public function disableInlineAlerts()
	{
		$this->disabledInlineAlerts = true;
		return $this;
	}

	public function setDataStructure(Mesour\Editable\Structures\IDataElementStructure $dataStructure)
	{
		$this->dataStructure = $dataStructure;
	}

	/**
	 * @return Mesour\Editable\Structures\IDataStructure
	 */
	public function getDataStructure()
	{
		if (!$this->dataStructure) {
			throw new Mesour\InvalidStateException('Data structure is required.');
		}
		return $this->dataStructure;
	}

	/**
	 * @return Mesour\Components\Utils\Html
	 */
	public function getControlPrototype()
	{
		return $this->getHtmlElement();
	}

	/**
	 * @return Modal
	 */
	public function getModal()
	{
		return $this['modal'];
	}

	public function setDisabled($disabled = true)
	{
		$this->disabled = (bool) $disabled;
		return $this;
	}

	public function isDisabled()
	{
		return $this->disabled;
	}

	public function create()
	{
		parent::create();

		if (
			$this->getDataStructure() instanceof Mesour\Editable\Structures\DataStructure
			&& !$this->getDataStructure()->getSource(false)
		) {
			throw new Mesour\InvalidStateException(
				sprintf('Source is required if use %s.', Mesour\Editable\Structures\DataStructure::class)
			);
		}

		$wrapper = $this->getControlPrototype();
		$oldWrapper = clone $wrapper;
		foreach ($oldWrapper->attrs as $key => $attr) {
			if (is_object($attr)) {
				$oldWrapper->attrs[$key] = clone $attr;
			}
		}

		$script = Mesour\Components\Utils\Html::el('script');
		$script->setHtml($this->createCoreScript());
		$wrapper->add($script);

		$modal = $this->getModal();

		$modal->getModalFooter()->addButton('saveButton')
			->setType('primary')
			->setAttribute('data-editable-form-save', 'true')
			->setText('Save');

		$modal->setAttribute('data-editable-modal', $this->createLinkName());

		$wrapper->add($this->getModal()->create());

		$this->onRender($this);

		$this->setHtmlElement($oldWrapper);
		return $wrapper;
	}

	public function handleEdit($name, $newValue, $oldValue = null, $identifier = null, array $params = [])
	{
		try {
			$currentField = $this->getDataStructure()->getField($name);

			PermissionsChecker::check(PermissionsChecker::EDIT, $this, $currentField);

			$this->onEditField(
				$currentField,
				$this->fixValue($currentField, $newValue),
				$this->fixValue($currentField, $oldValue),
				is_numeric($identifier) ? (int) $identifier : $identifier,
				$params
			);
		} catch (Mesour\Editable\ValidatorException $e) {
			$this->processError($e);
		}
	}

	public function handleEditForm(
		$name,
		$identifier,
		array $values,
		array $oldValues,
		array $params = [],
		array $reference = []
	) {
		try {
			$currentField = $this->getDataStructure()->getField($name);

			PermissionsChecker::check(PermissionsChecker::EDIT_FORM, $this, $currentField);

			$fieldReference = $currentField->getReference();

			foreach ($this->getDataStructure()->getElement($fieldReference['table'])->getFields() as $field) {
				$values[$field->getName()] = $this->fixValue($field, $values[$field->getName()]);
				$oldValues[$field->getName()] = $this->fixValue($field, $oldValues[$field->getName()]);
			}

			$this->fixReferenceColumn($values, $fieldReference, 'primary_key');
			$this->fixReferenceColumn($oldValues, $fieldReference, 'primary_key');

			$this->fixReferenceColumn($values, $fieldReference, 'column');
			$this->fixReferenceColumn($oldValues, $fieldReference, 'column');

			if ($reference) {
				$referenceInstance = new Mesour\Editable\Structures\Reference(
					$reference['column']['name'],
					$reference['selfColumn']['name'],
					$reference['column']['value'],
					$reference['selfColumn']['value']
				);
			} else {
				$fieldReference = $currentField->getReference();
				$referenceInstance = new Mesour\Editable\Structures\Reference(
					$fieldReference['column'],
					$fieldReference['primary_key'],
					$identifier,
					$values[$fieldReference['primary_key']]
				);
			}

			$this->onEditElement(
				$currentField,
				$values,
				$oldValues,
				$referenceInstance,
				is_numeric($identifier) ? (int) $identifier : $identifier,
				$params
			);
		} catch (Mesour\Editable\ValidatorException $e) {
			$this->processError($e);
		}
	}

	public function handleCreate($name, array $values, array $params = [], $identifier = null)
	{
		try {
			$currentField = $this->getDataStructure()->getField($name);

			PermissionsChecker::check(PermissionsChecker::CREATE, $this, $currentField);

			$reference = $currentField->getReference();

			$newValues = [];
			foreach ($this->getDataStructure()->getElement($reference['table'])->getFields() as $field) {
				if (isset($values[$field->getName()])) {
					$newValues[$field->getName()] = $this->fixValue($field, $values[$field->getName()]);
				}
			}

			$this->onCreate(
				$currentField,
				$newValues,
				is_numeric($identifier) ? (int) $identifier : $identifier,
				$params
			);
		} catch (Mesour\Editable\ValidatorException $e) {
			$this->processError($e);
		}
	}

	public function handleRemove($name, $identifier, $value)
	{
		try {
			$currentField = $this->getDataStructure()->getField($name);

			PermissionsChecker::check(PermissionsChecker::REMOVE, $this, $currentField);

			$this->onRemove(
				$currentField,
				is_numeric($value) ? (int) $value : $value,
				is_numeric($identifier) ? (int) $identifier : $identifier
			);
		} catch (Mesour\Editable\ValidatorException $e) {
			$this->processError($e);
		}
	}

	public function handleAttach($name, array $reference, $identifier = null, array $params = [])
	{
		try {
			$currentField = $this->getDataStructure()->getField($name);

			PermissionsChecker::check(PermissionsChecker::ATTACH, $this, $currentField);

			$referenceInstance = new Mesour\Editable\Structures\Reference(
				$reference['column']['name'],
				$reference['selfColumn']['name'],
				$reference['column']['value'],
				$reference['selfColumn']['value']
			);
			if (!$referenceInstance->getToValue()) {
				throw new Mesour\Editable\ValidatorException('Please select one item');
			}

			$this->onAttach($currentField, $referenceInstance, $identifier, $params);
		} catch (Mesour\Editable\ValidatorException $e) {
			$this->processError($e);
		}
	}

	public function triggerFieldError($fieldName, $message)
	{
		$e = new Mesour\Editable\ValidatorException($message);
		$e->setFieldName($fieldName);
		throw $e;
	}

	public function handleReferenceData($table, $referencedTable = null)
	{
		$source = $this->getDataStructure()
			->getSource()
			->getReferencedSource($table);

		$this->getPayload()->set('data', $this->fixDataForPayload($source->fetchAll()));

		if ($referencedTable) {
			$referencedSource = $this->getDataStructure()
				->getSource()
				->getReferencedSource($referencedTable);
			$this->getPayload()->set('reference', $this->fixDataForPayload($referencedSource->fetchAll()));
		}

		$this->getPayload()->sendPayload();
	}

	public function handleDataStructure()
	{
		$data = $this->getDataStructure()->toArray();

		if (isset($data['fields'])) {
			foreach ($data['fields'] as $key => $field) {
				if (isset($data['fields'][$key]['title'])) {
					$data['fields'][$key]['title'] = $this->getTranslator()->translate($data['fields'][$key]['title']);
				}
			}
		}

		$this->getPayload()->set('data', $data);
		$this->getPayload()->sendPayload();
	}

	protected function fixDataForPayload(array $data)
	{
		$out = [];
		foreach ($data as $key => $row) {
			$rowData = $row;
			foreach ($row as $column => $value) {
				if ($value instanceof \DateTime) {
					$rowData[$column] = $value->format('Y-m-d H:i:s');
				}
			}
			$out[] = $rowData;
		}
		return $out;
	}

	protected function checkField(Mesour\Editable\Structures\Fields\IStructureField $structureField)
	{
		if ($structureField->isDisabled()) {
			throw new Mesour\InvalidStateException(
				sprintf('Field %s has not enabled attach existing row.', $structureField->getName())
			);
		}
	}

	protected function createCoreScript()
	{
		$outScript = 'var mesour = !mesour ? {} : mesour;';
		$outScript .= 'mesour.editable = !mesour.editable ? [] : mesour.editable;';

		$outScript .= sprintf(
			'mesour.editable.push(["enable","%s","%s","%s"]);',
			$this->createLinkName(),
			$this->isInline() ? 'true' : 'false',
			$this->isDisabledInlineAlerts() ? 'true' : 'false'
		);

		$translates = [
			'select' => $this->getTranslator()->translate('Select...'),
			'selectOne' => $this->getTranslator()->translate('Select one'),
			'selectExisting' => $this->getTranslator()->translate('Select from existing'),
			'allSelected' => $this->getTranslator()->translate('All existing companies are attachet to this client...'),
			'attachExisting' => $this->getTranslator()->translate('Attach existing'),
			'createNew' => $this->getTranslator()->translate('Create new'),
			'dataSaved' => $this->getTranslator()->translate('Successfuly saved'),
			'invalidNumber' => $this->getTranslator()->translate('Value must be valid number'),
			'statusError' => $this->getTranslator()->translate('ERROR! Status: %status%. Try save data later.'),
			'emptyValue' => $this->getTranslator()->translate('- none'),
			'saveEmptyValue' => $this->getTranslator()->translate('Really save empty value?'),
			'saveItem' => $this->getTranslator()->translate('Save'),
			'cancelEdit' => $this->getTranslator()->translate('Cancel'),
			'editItem' => $this->getTranslator()->translate('Edit in form'),
			'reset' => $this->getTranslator()->translate('Cancel'),
			'emptyButton' => $this->getTranslator()->translate('Set empty value'),
		];
		$outScript .= 'mesour.editable.push(["setTranslations",' . Json::encode($translates) . ']);';

		return $outScript;
	}

	protected function fixValue(Mesour\Editable\Structures\Fields\IStructureField $elementField, $value)
	{
		if (is_null($value)) {
			return null;
		}

		if ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::TEXT) {
			return Strings::trim($value);
		} elseif ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::ENUM) {
			/** @var Mesour\Editable\Structures\Fields\EnumField $elementField */
			$values = $elementField->getValues();
			$isValueAllowed = isset($values[$value]);
			if ($elementField->isNullable() && !$value && !$isValueAllowed) {
				return null;
			}
			if (!$isValueAllowed) {
				throw new Mesour\OutOfRangeException(
					sprintf('Enum value %s does not exist on %s field.', $value, $elementField->getName())
				);
			}
			return $value;
		} elseif ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::NUMBER) {
			/** @var Mesour\Editable\Structures\Fields\NumberField $elementField */
			if (!$value && !is_numeric($value)) {
				if (!$elementField->isNullable()) {
					return 0;
				}
				return null;
			}

			if (
				(method_exists($elementField, 'getDecimals') && $elementField->getDecimals() === 0)
				|| strpos($value, '.') === -1
			) {
				return (int) $value;
			}
			return (double) $value;
		} elseif ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::DATE) {
			/** @var Mesour\Editable\Structures\Fields\DateField $elementField */
			if (!$value && $elementField->isNullable()) {
				return null;
			} elseif ($value && !$value instanceof \DateTime) {
				try {
					if (is_numeric($value)) {
						$dateTime = new \DateTime();
						$dateTime->setTimestamp($value);
						return $dateTime;
					}
					return new \DateTime($value);
				} catch (\Exception $e) {
					$exception = new Mesour\Editable\InvalidDateTimeException('Date is invalid');
					$exception->setFieldName($elementField->getName());
					throw $exception;
				}
			}
		} elseif ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::ONE_TO_ONE) {
			/** @var Mesour\Editable\Structures\Fields\OneToOneField $elementField */
			if (!$value && $elementField->isNullable()) {
				return null;
			}
		} elseif ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::BOOL) {
			/** @var Mesour\Editable\Structures\Fields\BoolField $elementField */
			if (Strings::length($value) === 0 && $elementField->isNullable()) {
				return null;
			}
			return !$value || $value === 'false' ? false : true;
		}
		return $value;
	}

	protected function processError(Mesour\Editable\ValidatorException $exception)
	{
		$this->getPayload()->set(
			'error',
			[
				'message' => $this->getTranslator()->translate($exception->getMessage()),
				'field' => $exception->getFieldName(),
			]
		);
		$this->getPayload()->sendPayload();
	}

	protected function fixReferenceColumn(&$values, $fieldReference, $key)
	{
		if (isset($values[$fieldReference[$key]]) && is_numeric($values[$fieldReference[$key]])) {
			$values[$fieldReference[$key]] = (int) $values[$fieldReference[$key]];
		}
	}

}
