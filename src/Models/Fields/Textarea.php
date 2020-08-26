<?php 

namespace Inspire\Forms\Models\Fields;

use Inspire\Forms\Models\FormSubmission;

class Textarea extends Text
{
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
	public function backendTemplate(): string
	{
		return 'inspireforms/cp/fields/textarea';
	}
}