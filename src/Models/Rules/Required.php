<?php 

namespace Inspire\Forms\Models\Rules;

use Inspire\Forms\Models\Rule;

class Required extends Rule
{
	public $requiresValue = false;

	public function name(): string 
	{
		return \Craft::t('inspireforms', 'Required');
	}

	public function handle(): string
	{
		return 'required';
	}

	public function defaultMessage(): string
	{
		return \Craft::t('inspireforms', '{field.name} is required.');
	}
}