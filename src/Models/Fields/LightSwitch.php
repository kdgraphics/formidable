<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FieldTypes\BooleanField;
use Ryssbowh\Formidable\Models\FormSubmission;

class LightSwitch extends Field
{
	protected $fieldTypeClass = BooleanField::class;

	public $label;
	public $required;
	public $enforceRequired;
	public $default;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'switch';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Switch';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'formidable/cp/fields/switch';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			['label', 'string'],
			['label', 'trim'],
			[['required', 'default'], 'boolean']
		];
	}

	/**
	 * inheritDoc
	 */
	public function getSiteRules(): array
	{
		$rules = [[$this->name, 'boolean']];
		if ($this->required) {
			$rules[] = [$this->name, 'compare', ['compareValue' => 1, 'operator' => '==', 'message' => \Craft::t('formidable', '{name} must be checked', ['name' => ucfirst($this->name)])]];
		}
		return $rules;
	}

	/**
	 * inheritDoc
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
		$submission->{$this->name} = $submission->{$this->name} ? \Craft::t('formidable', 'Yes') : \Craft::t('formidable', 'No');
	}
}