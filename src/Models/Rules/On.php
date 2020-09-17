<?php 

namespace Inspire\Forms\Models\Rules;

use Inspire\Forms\Models\Rule;

class On extends Rule
{
	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('inspireforms', 'On');
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
		return \Craft::t('inspireforms', '{field.name} must be on.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('inspireforms', 'is on');
	}
}