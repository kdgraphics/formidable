<?php 

namespace Inspire\Forms\Models\Fields;

use Inspire\Forms\Models\Field;
use Inspire\Forms\Models\FormSubmission;

class LightSwitch extends Field
{
	public $label;
	public $required;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'switch';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Switch';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'inspireforms/cp/fields/switch';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			['label', 'string'],
			['label', 'trim'],
			['required', 'boolean']
		];
	}

	/**
	 * inheritDoc
	 */
	public function getSiteRules(): array
	{
		$rules = [$this->name, 'boolean'];
		if ($this->required) {
			$rules[] = [$this->name, 'compare', 'compareValue' => 1, 'operator' => '=='];
		}
		return $rules;
	}
}