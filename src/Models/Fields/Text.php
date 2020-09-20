<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FormSubmission;

class Text extends Field
{
	public $placeholder;
	public $label;
	public $default;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'text';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Text';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'formidable/cp/fields/text';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['placeholder','label', 'default'], 'string'],
			[['placeholder','label', 'default'], 'trim'],
		];
	}

	/**
	 * inheritDoc
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		return [
			[$this->name, 'string'], 
			[$this->name, 'trim']
		];
	}
}