<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Models\Rule;

class On extends Rule
{
	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'On');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'on';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} must be on.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'is on');
	}
}