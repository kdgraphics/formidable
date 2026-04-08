<?php

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\models\Field;
use Inspire\Formidable\models\FieldTypes\ArrayField;
use Inspire\Formidable\models\FormSubmission;
use craft\elements\Category;

class CheckboxCategory extends Field
{
    protected $fieldTypeClass = ArrayField::class;

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
        return 'checkbox_category';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Checkbox Category';
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
            ['groups', 'safe']
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

    /**
     * @inheritDoc
     */
    public static function getHtmlValue($value): string
    {
        if (!$ids = self::validatedIDs($value)) {
            return '';
        }
        $categories = Category::find()->id($ids)->all();
        $html = [];
        foreach ($categories as $category) {
            $html[] = '<a target="_blank" href="' . $category->getCpEditUrl() . '">' . $category->title . '</a>';
        }
        return implode('<br/>', $html);
    }

    /**
     * @inheritDoc
     */
    public static function getStringValue($value): string
    {
        if (!$ids = self::validatedIDs($value)) {
            return '';
        }
        $categories = Category::find()->id($ids)->all();
        $string = [];
        foreach ($categories as $category) {
            $string[] = $category->title;
        }
        return implode(', ', $string);
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
