<?php 

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\models\FormSubmission;

class Date extends Text
{
	public $format = 'd/m/Y';
	public $disableMobile = false;
	public $enableTime = false;
	public $enableSeconds = false;
	public $maxDate = '';
	public $minDate = '';
	public $minuteIncrement = 5;
	public $noCalendar = false;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'date';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Date';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return array_merge(parent::getExtraCpRules(), [
			[['format', 'maxDate', 'minDate'], 'string'],
			[['format', 'maxDate', 'minDate'], 'trim'],
			['minuteIncrement', 'integer'],
			[['disableMobile', 'enableTime', 'enableSeconds', 'noCalendar'], 'boolean', 'trueValue' => true, 'falseValue' => false],
		]);
	}

	public function getDatepickerOptions()
	{
		return [
			'dateFormat' => $this->format,
			'disableMobile' => $this->disableMobile,
			'enableTime' => $this->enableTime,
			'enableSeconds' => $this->enableSeconds,
			'maxDate' => $this->maxDate,
			'minDate' => $this->minDate,
			'minuteIncrement' => $this->minuteIncrement,
			'noCalendar' => $this->noCalendar
		];
	}
}
