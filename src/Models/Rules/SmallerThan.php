<?php 

namespace Inspire\Forms\Models\Rules;

use Inspire\Forms\Models\Rule;

class SmallerThan extends Rule
{
	public function name(): string 
	{
		return \Craft::t('inspireforms', 'Smaller than');
	}

	public function handle(): string
	{
		return 'smallerThan';
	}

	public function defaultMessage(): string
	{
		return \Craft::t('inspireforms', '{field.name} must be bigger than {rule.value}.');
	}
}