<?php

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FormSubmission;
use craft\elements\Entry;

class RadioEntry extends Field
{
    public $label;
    public $sections = [];
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
        return 'radio-entry';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Radio Entry';
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
            ['sections', 'required'],
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
        $entry = Entry::find()->id($value)->one();
        if ($entry) {
            return '<a target="_blank" href="'.$entry->getCpEditUrl().'">'.$entry->title.'</a>'; 
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
        $entry = Entry::find()->id($value)->one();
        if ($entry) {
            return $entry->title;
        }
        return '';
    }

    /**
     * Load all categories from the selected group
     * 
     * @return array
     */
    public function getAllEntries(array $eagerLoad = []): array
    {
        $sections = array_map(function ($uid) {
            return \Craft::$app->sections->getSectionByUid($uid);
        }, $this->sections);
        $out = [];
        foreach ($sections as $section) {
            $entries = Entry::find()->section($section)->with($eagerLoad)->all();
            foreach ($entries as $entry) {
                $out[] = $entry;
            }
        }
        return $out;
    }
}
