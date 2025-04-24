<?php 

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\FormSubmission;

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
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		$rules = parent::getFrontEndRules($submission);
		$rules[] = [$this->handle, 'email'];
		return $rules;
	}

	/**
	 * inheritDoc
	 */
	public static function getHtmlValue($value): string
	{
		return '<a href="mailto:'.$value.'">'.$value.'</a>';
	}
}
