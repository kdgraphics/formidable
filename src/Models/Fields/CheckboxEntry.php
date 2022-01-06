<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FieldTypes\ArrayField;
use Ryssbowh\Formidable\Models\FormSubmission;
use craft\elements\Entry;

class CheckboxEntry extends SelectEntry
{
	protected $fieldTypeClass = ArrayField::class;

	public $choiceContainerClass;
	public $choiceLabelClass;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'checkbox_entry';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Checkbox Entry';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['label', 'tooltip', 'tooltipLabel', 'choiceContainerClass', 'choiceLabelClass'], 'string'],
			[['label', 'tooltip', 'tooltipLabel', 'choiceContainerClass', 'choiceLabelClass'], 'trim'],
			[['labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false],
			['sections', 'safe']
		];
	}

	/**
	 * inheritDoc
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		return [
			[$this->handle, 'each', 'rule' => ['integer']]
		];
	}
}