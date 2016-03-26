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

/**
 * @author Matouš Němec (http://mesour.com)
 *
 * @method null onRender(Editable $editable)
 * @method null onCreate(\Mesour\Editable\Structures\Fields\IStructureElementField $field, array $newValues, $identifier = null, array $params = [])
 * @method null onRemove(\Mesour\Editable\Structures\Fields\IStructureElementField $field, $value, $identifier = null)
 * @method null onAttach(\Mesour\Editable\Structures\Fields\IStructureElementField $field, \Mesour\Editable\Structures\Reference $reference, $identifier = null, array $params = [])
 * @method null onEditField(\Mesour\Editable\Structures\Fields\IStructureField $field, $newValue, $oldValue, $identifier = null, array $params = [])
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

	public function handleEdit($name, $newValue, $oldValue, $identifier = null, array $params = [])
	{
		try {
			$currentField = $this->getDataStructure()->getField($name);

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
	)
	{
		try {
			$currentField = $this->getDataStructure()->getField($name);
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

		$this->getPayload()->set('data', $source->fetchAll());

		if ($referencedTable) {
			$referencedSource = $this->getDataStructure()
				->getSource()
				->getReferencedSource($referencedTable);
			$this->getPayload()->set('reference', $referencedSource->fetchAll());
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

	protected function createCoreScript()
	{
		$outScript = 'var mesour = !mesour ? {} : mesour;';
		$outScript .= 'mesour.editable = !mesour.editable ? [] : mesour.editable;';

		$outScript .= 'mesour.editable.push(["enable","' . $this->createLinkName() . '"]);';

		$translates = [
			'select' => $this->getTranslator()->translate('Select...'),
			'selectOne' => $this->getTranslator()->translate('Select one'),
			'selectExisting' => $this->getTranslator()->translate('Select from existing'),
			'allSelected' => $this->getTranslator()->translate('All existing companies are attachet to this client...'),
			'attachExisting' => $this->getTranslator()->translate('Attach existing'),
			'createNew' => $this->getTranslator()->translate('Create new'),
			'dataSaved' => $this->getTranslator()->translate('Successfuly saved'),
			'invalidNumber' => $this->getTranslator()->translate('Value must be valid number'),
		];
		$outScript .= 'mesour.editable.push(["setTranslations",' . Json::encode($translates) . ']);';

		return $outScript;
	}

	protected function fixValue(Mesour\Editable\Structures\Fields\IStructureField $elementField, $value)
	{
		if ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::TEXT) {
			return Strings::trim($value);
		} elseif ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::NUMBER) {
			if (
				(method_exists($elementField, 'getDecimals') && $elementField->getDecimals() === 0)
				|| strpos($value, '.') === -1
			) {
				return (int) $value;
			}
			return (double) $value;
		} elseif ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::DATE) {
			if ($value && !$value instanceof \DateTime) {
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
		} elseif ($elementField->getType() === Mesour\Sources\Structures\Columns\IColumnStructure::BOOL) {
			return !$value || $value === 'false' ? false : true;
		}
		return $value;
	}

	protected function processError(Mesour\Editable\ValidatorException $exception)
	{
		$this->getPayload()->set('error', [
			'message' => $this->getTranslator()->translate($exception->getMessage()),
			'field' => $exception->getFieldName(),
		]);
		$this->getPayload()->sendPayload();
	}

	protected function fixReferenceColumn(&$values, $fieldReference, $key)
	{
		if (isset($values[$fieldReference[$key]]) && is_numeric($values[$fieldReference[$key]])) {
			$values[$fieldReference[$key]] = (int) $values[$fieldReference[$key]];
		}
	}

}
