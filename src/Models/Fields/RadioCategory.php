<?php 

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FormSubmission;
use craft\elements\Category;

class RadioCategory extends Field
{
	public $label;
	public $groups = [];
	public $selectLabel;
	public $labelHtml = false;
	public $tooltip;
	public $tooltipLabel;
	public $choiceContainerClass;
	public $choiceLabelClass;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'radio_category';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Radio Category';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['label', 'selectLabel', 'tooltip', 'tooltipLabel'], 'string'],
			[['label', 'tooltip', 'tooltipLabel'], 'trim'],
			[['labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false],
			['groups', 'safe']
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
	 * @inheritDoc
	 */
	public static function getHtmlValue($value): string
	{
        if (!$ids = self::validatedIDs($value)) {
            return '';
        }
        $category = Category::find()->id($ids)->one();
		if ($category) {
			return '<a target="_blank" href="'.$category->getCpEditUrl().'">'.$category->title.'</a>'; 
		}
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public static function getStringValue($value): string
	{
        if (!$ids = self::validatedIDs($value)) {
            return '';
        }
		$category = Category::find()->id($ids)->one();
		if ($category) {
			return $category->title;
		}
		return '';
	}

	/**
	 * Load all categories from the selected group
	 * 
	 * @return array
	 */
	public function getCategories(): array
	{
		$categories = Category::find()->group($this->groups)->all();
		$out = [];
		foreach ($categories as $category) {
			$out[$category->id] = $category->title;
		}
		return $out;
	}
}
