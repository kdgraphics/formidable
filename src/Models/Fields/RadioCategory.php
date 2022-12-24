<?php

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FormSubmission;
use craft\elements\Category;

class RadioCategory extends Field
{
    public $label;
    public $groups = [];
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
        return 'radio-category';
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
            [['label', 'tooltip', 'tooltipLabel'], 'string'],
            [['label', 'tooltip', 'tooltipLabel'], 'trim'],
            [['labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false],
            ['groups', 'required'],
            [['choiceLabelClass', 'choiceContainerClass'], 'validateHtmlClasses']
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
        if (!$value) {
            return '';
        }
        $category = Category::find()->id($value)->one();
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
        if (!$value) {
            return '';
        }
        $category = Category::find()->id($value)->one();
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
