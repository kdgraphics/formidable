<?php 

namespace Inspire\Forms\Models\Fields;

use Inspire\Forms\Models\FormSubmission;

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
		return 'inspireforms/cp/fields/email';
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