<?php

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\models\Field;
use Inspire\Formidable\models\FormSubmission;
use craft\elements\Category;

class SelectCategory extends Field
{
    public $label;
    public $groups = [];
    public $includeSelect;
    public $selectLabel;
    public $labelHtml = false;
    public $multiple = false;
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
    public function getExtraCpRules(): array
    {
        return [
            [['label', 'selectLabel', 'tooltip', 'tooltipLabel'], 'string'],
            [['label', 'tooltip', 'tooltipLabel'], 'trim'],
            [['includeSelect', 'labelHtml', 'multiple'], 'boolean', 'trueValue' => true, 'falseValue' => false],
            ['groups', 'safe']
        ];
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormSubmission $submission): array
    {
        if ($this->multiple) {
            return [
                [$this->handle, 'each', 'rule' => ['integer']]
            ];
        }
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

    /**
     * inheritDoc
     */
    public function afterSubmissionValidated(FormSubmission $submission)
    {
        if (!$submission->{$this->handle}) {
            $submission->{$this->handle} = [];
        }
        $submission->{$this->handle} = is_array($submission->{$this->handle}) ? $submission->{$this->handle} : [$submission->{$this->handle}];
    }
}
