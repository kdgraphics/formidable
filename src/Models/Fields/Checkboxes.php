<?php 

namespace Inspire\Forms\Models\Fields;

use Inspire\Forms\Models\FormSubmission;

class Checkboxes extends Radio
{
	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'checkboxes';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Checkboxes';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'inspireforms/cp/fields/checkboxes';
	}

	/**
	 * inheritDoc
	 */
	public function getSiteRules(): array
	{
		$rules = [
			[$this->name, function ($attribute, $params, $validator) {
				if (!is_array($this->$attribute)) {
					$this->addError($attribute, \Craft::t('inspireforms', 'Value is invalid'));
				}
			}]
		];
		if ($this->required) {
			$rules[] = [$this->name, 'required'];
		}
		$rules[] = [$this->name, 'each', ['rule' => ['in', 'range' => $this->allValues()]]];
		return $rules;
	}

	/**
	 * inheritDoc
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
		$rawValue = $submission->{$this->name};
		if (!$rawValue) {
			return;
		}
		$choices = $this->buildChoices();
		$selected = [];
		foreach ($rawValue as $value) {
			$selected[] = $choices[$value];
		}
		$submission->{$this->name} = implode(', ', $selected);
	}
}