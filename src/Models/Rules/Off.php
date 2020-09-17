<?php 

namespace Inspire\Forms\Models\Rules;

use Inspire\Forms\Models\Rule;

class Off extends Rule
{
	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('inspireforms', 'Off');
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
		return \Craft::t('inspireforms', '{field.name} must be off.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('inspireforms', 'is off');
	}
}