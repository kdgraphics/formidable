<?php 

namespace Inspire\Forms\Models\Fields;

use Inspire\Forms\Models\Field;
use Inspire\Forms\Models\FormSubmission;

class Text extends Field
{
	public $placeholder;
	public $label;
	public $required;
	public $enforceRequired;
	public $maxLength;
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
		return 'inspireforms/cp/fields/text';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['placeholder','label', 'default'], 'string'],
			[['placeholder','label', 'default'], 'trim'],
			['maxLength', 'number'],
			[['required', 'enforceRequired'], 'boolean']
		];
	}

	/**
	 * inheritDoc
	 */
	public function getSiteRules(): array
	{
		$rules = [
			[$this->name, 'string', ['length' => $this->maxLength ? [0, $this->maxLength] : null]], 
			[$this->name, 'trim']
		];
		if ($this->required) {
			$rules[] = [$this->name, 'required'];
		}
		return $rules;
	}
}