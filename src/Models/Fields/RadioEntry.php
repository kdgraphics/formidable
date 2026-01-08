<?php

namespace Ryssbowh\Formidable\Models\Fields;

use Craft;
use craft\elements\Entry;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FormSubmission;

class RadioEntry extends Field
{
	public $label;
	public $sections = [];
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
		return 'radio_entry';
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
			[['label', 'selectLabel', 'tooltip', 'tooltipLabel'], 'string'],
			[['label', 'tooltip', 'tooltipLabel'], 'trim'],
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

        $siteId = Formidable::$plugin->submissions->currentSiteId;
        $entry = Entry::find()
            ->id($ids)
            ->siteId($siteId)
            ->one();

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
        if (!$ids = self::validatedIDs($value)) {
            return '';
        }

        $siteId = Formidable::$plugin->submissions->currentSiteId;
        $entry = Entry::find()
            ->id($ids)
            ->siteId($siteId)
            ->one();

        if ($entry) {
            return $entry->title;
        }

        return '';
    }

    /**
     * Load all categories from the selected group
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
}
