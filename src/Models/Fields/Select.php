<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FieldTypes\TextField;
use Ryssbowh\Formidable\Models\FormSubmission;

class Select extends Field
{
	public $label;
	public $choices;
	public $default;

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
		return 'formidable/cp/fields/select';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['label', 'default'], 'string'],
			[['label', 'default'], 'trim'],
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
				$this->addError(\Craft::t($attribute, 'formidable', 'Value is missing'));
			}
			if (!$choice['label']) {
				$this->addError($attribute, \Craft::t('formidable', 'Label is missing'));
			}
			if (in_array($choice['value'], $values)) {
				$value = $choice['value'] ?? '{empty}';
				$this->addError($attribute, \Craft::t('formidable', 'Value {value} is used twice', ['value' => $value]));
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
	public function getFrontEndRules(FormSubmission $submission): array
	{
		return [
			[$this->handle, 'string'],
			[$this->handle, 'trim'],
			[$this->handle, 'in', 'range' => $this->allValues()]
		];
	}

	/**
	 * inheritDoc
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
		$submission->{$this->handle} = $submission->{$this->handle} ? $this->buildChoices()[$submission->{$this->handle}] : '';
	}
}