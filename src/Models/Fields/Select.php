<?php 

namespace Inspire\Forms\Models\Fields;

use Inspire\Forms\Models\Field;
use Inspire\Forms\Models\FormSubmission;

class Select extends Field
{
	public $label;
	public $required;
	public $enforceRequired;
	public $choices;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'select';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Select';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'inspireforms/cp/fields/select';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['label'], 'string'],
			[['label'], 'trim'],
			[['required', 'enforceRequired'], 'boolean'],
			['choices', 'required'],
			['choices', 'validateChoices']
		];
	}

	/**
	 * Validate the choices
	 * 
	 * @param string $attribute
	 * @param array $param
	 * @param $validator
	 */
	public function validateChoices($attribute, $param, $validator)
	{
		$choices = $this->$attribute;
		$values = [];
		foreach ($choices as $index => $choice) {
			if ($index != 0 and !$choice['value']) {
				$this->addError(\Craft::t($attribute, 'inspireforms', 'Value is missing'));
			}
			if (!$choice['label']) {
				$this->addError($attribute, \Craft::t('inspireforms', 'Label is missing'));
			}
			if (in_array($choice['value'], $values)) {
				$value = $choice['value'] ?? '{empty}';
				$this->addError($attribute, \Craft::t('inspireforms', 'Value {value} is used twice', ['value' => $value]));
			}
			$values[] = $choice['value'];
		}
	}

	/**
	 * Build choices as an array 'value' => 'label'
	 * @return array
	 */
	public function buildChoices(): array
	{
		$choices = [];
		foreach ($this->choices as $choice) {
			$choices[$choice['value']] = $choice['label'];
		}
		return $choices;
	}

	/**
	 * Returns all values
	 * 
	 * @return array
	 */
	public function allValues(): array
	{
		$values = [];
		foreach ($this->choices as $choice) {
			$values[] = $choice['value'];
		}
		return $values;
	}

	/**
	 * inheritDoc
	 */
	public function getSiteRules(): array
	{
		$rules = [
			[$this->name, 'string'],
			[$this->name, 'trim']
		];
		if ($this->required) {
			$rules[] = [$this->name, 'required'];
		}
		$rules[] = [$this->name, 'in', ['range' => $this->allValues()]];
		return $rules;
	}

	/**
	 * inheritDoc
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
		$submission->{$this->name} = $submission->{$this->name} ? $this->buildChoices()[$submission->{$this->name}] : '';
	}
}