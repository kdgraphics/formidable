<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FormSubmission;
use craft\elements\Entry;

class SelectEntry extends Field
{
	public $label;
	public $sections = [];
	public $includeSelect;
	public $selectLabel;
	public $labelHtml = false;
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
			[['label', 'selectLabel', 'tooltip', 'tooltipLabel'], 'string'],
			[['label', 'tooltip', 'tooltipLabel'], 'trim'],
			[['includeSelect', 'labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false],
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
	 * inheritDoc
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
		$entry = Entry::find()->id($submission->{$this->handle})->one();
		if ($entry) {
			$submission->{$this->handle} = $entry->title;
		} else {
			$submission->{$this->handle} = '';
		}
	}

	/**
	 * get all sections
	 * 
	 * @return array
	 */
	public function getEntries(): array
	{
		$sections = array_map(function ($uid) {
			return \Craft::$app->sections->getSectionByUid($uid);
		}, $this->sections);
		$out = [];
		foreach ($sections as $section) {
			$entries = Entry::find()->section($section)->all();
			foreach ($entries as $entry) {
				$out[$entry->id] = $entry->title;
			}
		}
		return $out;
	}
}