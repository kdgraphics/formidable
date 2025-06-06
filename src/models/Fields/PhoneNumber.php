<?php 

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\models\Fields\Text;
use Inspire\Formidable\models\FormSubmission;

class PhoneNumber extends Text
{
	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'phone_number';
	}

	/**
	 * inheritDoc
	 **/
	public function getValue()
	{
		return $this->_value ?? $this->default;
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Phone Number';
	}

	/**
	 * inheritDoc
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		return [
			[$this->handle, 'string'], 
			[$this->handle, 'trim']
		];
	}
}
