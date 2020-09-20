<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FieldTypes\NumberField;
use Ryssbowh\Formidable\Models\FormSubmission;

class Number extends Text
{
	protected $fieldTypeClass = NumberField::class;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'number'; 
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Number';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'formidable/cp/fields/number';
	}

	/**
	 * inheritDoc
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		return [[$this->name, 'number']];
	}
}