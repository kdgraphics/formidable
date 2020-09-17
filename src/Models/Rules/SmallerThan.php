<?php 

namespace Inspire\Forms\Models\Rules;

use Inspire\Forms\Models\Rule;

class SmallerThan extends Rule
{
	public $requiresValue = true;

	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('inspireforms', 'Smaller than');
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
		return \Craft::t('inspireforms', '{field.name} must be bigger than {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('inspireforms', 'smaller than');
	}
}