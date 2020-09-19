<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Models\Rule;

class Required extends Rule
{
	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Required');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'required';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} is required.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'present');
	}
}