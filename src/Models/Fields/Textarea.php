<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\FormSubmission;

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
		return 'formidable/cp/fields/textarea';
	}
}