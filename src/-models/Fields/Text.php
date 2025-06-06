<?php 

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\models\Field;
use Inspire\Formidable\models\FormSubmission;

class Text extends Field
{
	public $placeholder;
	public $label;
	public $default;
	public $labelHtml = false;
	public $tooltip;
	public $tooltipLabel;

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
	public function getValue()
	{
		return $this->_value ?? $this->default;
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
	public function getExtraCpRules(): array
	{
		return [
			[['placeholder','label', 'default', 'tooltip', 'tooltipLabel'], 'string'],
			[['placeholder','label', 'default', 'tooltip', 'tooltipLabel'], 'trim'],
			['labelHtml', 'boolean', 'trueValue' => true, 'falseValue' => false]
		];
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
