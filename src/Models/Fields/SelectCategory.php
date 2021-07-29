<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FormSubmission;
use craft\elements\Category;

class SelectCategory extends Field
{
	public $label;
	public $category;
	public $optionsLabel;
	public $includeSelect;
	public $selectLabel;
	public $labelHtml = false;

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
		return 'formidable/cp/fields/select-entry';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['label', 'optionsLabel', 'category', 'selectLabel'], 'string'],
			[['label'], 'trim'],
			[['includeSelect', 'labelHtml'], 'boolean']
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
			$submission->{$this->handle} = $category->{$this->optionsLabel};
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
			$out[$category->id] = $category->{$this->optionsLabel};
		}
		return $out;
	}
}