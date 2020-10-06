<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Exceptions\RuleException;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Models\FieldTypes\ArrayField;
use Ryssbowh\Formidable\Models\FieldTypes\FileField;
use Ryssbowh\Formidable\Models\FieldTypes\NumberField;
use Ryssbowh\Formidable\Models\FieldTypes\TextField;
use Ryssbowh\Formidable\Models\Rule;

class Required extends Rule
{
	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Required');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'required';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} is required.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'is present');
	}

	/**
	 * @inheritDoc
	 */
	public function validFieldTypes(): array
	{
		return [ArrayField::class, FileField::class, TextField::class, NumberField::class];
	}

	/**
	 * @inheritDoc
	 */
	public function check(FieldInterface $field, $value, $against = null): bool
	{
		switch (get_class($field->fieldType())) {
			case ArrayField::class:
				return (is_array($value) and sizeof($value) > 0);
			case FileField::class:
				return true;
			case TextField::class:
			case NumberField::class:
				return strlen(trim($value)) > 0;
		}
		throw RuleException::typeNotValid($rule, get_class($field->fieldType()));
	}

	/**
	 * inheritDoc
	 */
	public function checkJs(): string
	{
		return "function (field, value, against) {
			switch (field.type) {
				case 'text':
				case 'number':
					return (value.trim().length > 0);
				case 'array':
					return (value.length > 0);
			}
			throw 'Field type '+field.type+' can\'t be checked for a required rule';
		}";
	}
}