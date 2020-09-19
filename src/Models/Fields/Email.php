<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\FormSubmission;

class Email extends Text
{
	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'email';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Email';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'formidable/cp/fields/email';
	}

	/**
	 * inheritDoc
	 */
	public function getSiteRules(): array
	{
		$rules = parent::getSiteRules();
		$rules[] = [$this->name, 'email'];
		return $rules;
	}
}