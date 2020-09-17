<?php 

namespace Inspire\Forms\Models\Rules;

use Inspire\Forms\Models\Rule;

class Contain extends Rule
{
	public $requiresValue = true;

	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('inspireforms', 'Contain');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'contain';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('inspireforms', '{field.name} must contain {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('inspireforms', 'contains');
	}
}