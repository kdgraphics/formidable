<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Models\Rule;

class EqualTo extends Rule
{
	public $requiresValue = true;

	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Equal to');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'equalTo';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} must equal to {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'equal to');
	}
}