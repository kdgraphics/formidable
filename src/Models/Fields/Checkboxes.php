<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\FormSubmission;

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
		return 'formidable/cp/fields/checkboxes';
	}

	/**
	 * inheritDoc
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		return [
			[$this->name, function ($attribute, $params, $validator) use ($submission) {
				if (!is_array($submission->$attribute)) {
					$this->addError($attribute, \Craft::t('formidable', 'Value is invalid'));
				}
			}],
			[$this->name, 'each', 'rule' => ['in', 'range' => $this->allValues()]]
		];
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