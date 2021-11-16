<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FormSubmission;
use craft\elements\Category;

class SelectCategory extends Field
{
	public $label;
	public $category;
	public $includeSelect;
	public $selectLabel;
	public $labelHtml = false;
	public $tooltip;
	public $tooltipLabel;
	//Unused, here for backward compatibility
	public $optionsLabel;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'select_category';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Select Category';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'formidable/cp/fields/select-category';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['label', 'category', 'selectLabel', 'tooltip', 'tooltipLabel'], 'string'],
			[['label', 'tooltip', 'tooltipLabel'], 'trim'],
			[['includeSelect', 'labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false]
		];
	}

	/**
	 * inheritDoc
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		return [
			[$this->handle, 'integer']
		];
	}

	/**
	 * inheritDoc
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
		$category = Category::find()->id($submission->{$this->handle})->one();
		if ($category) {
			$submission->{$this->handle} = $category->title;
		} else {
			$submission->{$this->handle} = '';
		}
	}

	/**
	 * Load all categories from the selected group
	 * 
	 * @return array
	 */
	public function getCategories(): array
	{
		$categories = Category::find()->group($this->category)->all();
		$out = [];
		foreach ($categories as $category) {
			$out[$category->id] = $category->title;
		}
		return $out;
	}
}
