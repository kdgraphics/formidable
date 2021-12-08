<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\FormSubmission;

class Textarea extends Text
{
	public $rows;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'textarea';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Text area';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		$rules = parent::getExtraCpRules();
		$rules[] = ['rows', 'number'];
		return $rules;
	}
}