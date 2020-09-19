<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Models\Rule;

class SmallerThan extends Rule
{
	public $requiresValue = true;

	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Smaller than');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'smallerThan';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} must be bigger than {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'smaller than');
	}
}