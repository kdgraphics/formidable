<?php 

namespace Inspire\Forms\Models\Fields;

use Inspire\Forms\Models\Field;

class Text extends Field
{
	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'text';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Text';
	}

	public function backendTemplate(): string
	{
		return 'inspireforms/cp/fields/text';
	}
}