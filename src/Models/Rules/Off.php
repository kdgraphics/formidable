<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Models\Rule;

class Off extends Rule
{
	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Off');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'off';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} must be off.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'is off');
	}
}