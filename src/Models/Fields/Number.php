<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FormSubmission;

class Number extends Text
{
	public $minLength;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'number'; 
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Number';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'formidable/cp/fields/number';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		$rules = parent::getExtraCpRules();
		$rules[] = ['minLength', 'number'];
		return $rules;
	}

	/**
	 * inheritDoc
	 */
	public function getSiteRules(): array
	{
		$length = null;
		if ($this->minLength or $this->maxLength) {
			$length = [$this->minLength ?? 0, $this->maxLength ?? null];
		}
		$rules = [
			[$this->name, 'string', ['length' => $length]], 
			[$this->name, 'trim']
		];
		if ($this->required) {
			$rules[] = [$this->name, 'required'];
		}
		return $rules;
	}
}