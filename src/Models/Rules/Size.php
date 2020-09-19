<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Models\Rule;

class Size extends Rule
{
	public $requiresValue = true;

	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Size');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'size';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', 'Size of {field.name} must be {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'is of size');
	}
}