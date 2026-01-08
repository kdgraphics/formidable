<?php

namespace Ryssbowh\Formidable\Models\Fields;

use Craft;
use craft\elements\Entry;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FormSubmission;

class SelectEntry extends Field
{
    public $label;
    public $sections = [];
    public $includeSelect;
    public $selectLabel;
    public $labelHtml = false;
    public $multiple = false;
    public $tooltip;
    public $tooltipLabel;

    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'select_entry';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Select Entry';
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
            ['sections', 'safe']
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
    public static function getStringValue($value): string
    {
        if (!$ids = self::validatedIDs($value)) {
            return '';
        }

        $siteId = Formidable::$plugin->submissions->currentSiteId;
        $entries = Entry::find()
            ->id($ids)
            ->siteId($siteId)
            ->all();

        $string = [];
        foreach ($entries as $entry) {
            $string[] = $entry->title;
        }

        return implode(', ', $string);
    }

    /**
     * @inheritDoc
     */
    public static function getHtmlValue($value): string
    {
        if (!$ids = self::validatedIDs($value)) {
            return '';
        }

        $siteId = Formidable::$plugin->submissions->currentSiteId;
        $entries = Entry::find()
            ->id($ids)
            ->siteId($siteId)
            ->all();

        $html = [];
        foreach ($entries as $entry) {
            $html[] = '<a target="_blank" href="'.$entry->getCpEditUrl().'">'.$entry->title.'</a>';
        }

        return implode('<br/>', $html);
    }

    /**
     * get all entries (id, title)
     *
     * @return array
     */
    public function getEntries(): array
    {
        $sections = array_map(function ($uid) {
            return Craft::$app->sections->getSectionByUid($uid);
        }, $this->sections);

        $siteId = Craft::$app->getSites()->getCurrentSite()->id;

        $out = [];
        foreach ($sections as $section) {
            $entries = Entry::find()
                ->section($section)
                ->siteId($siteId)
                ->all();

            foreach ($entries as $entry) {
                $out[$entry->id] = $entry->title;
            }
        }

        return $out;
    }

    /**
     * get all entries
     *
     * @param array $eagerLoad
     * @return array
     */
    public function getAllEntries(array $eagerLoad = []): array
    {
        $sections = array_map(function ($uid) {
            return Craft::$app->sections->getSectionByUid($uid);
        }, $this->sections);

        $siteId = Craft::$app->getSites()->getCurrentSite()->id;

        $out = [];
        foreach ($sections as $section) {
            $entries = Entry::find()
                ->section($section)
                ->siteId($siteId)
                ->with($eagerLoad)
                ->all();

            foreach ($entries as $entry) {
                $out[] = $entry;
            }
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
