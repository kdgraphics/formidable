<?php 

namespace Inspire\Formidable\Models\Rules;

use Inspire\Formidable\Exceptions\RuleException;
use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Models\FieldTypes\ArrayField;
use Inspire\Formidable\Models\FieldTypes\FileField;
use Inspire\Formidable\Models\FieldTypes\NumberField;
use Inspire\Formidable\Models\FieldTypes\TextField;
use Inspire\Formidable\Models\Rule;

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
			case FileField::class:
				return !empty($value);
			case TextField::class:
			case NumberField::class:
				return strlen(trim($value)) > 0;
		}
		throw RuleException::typeNotValid($this, get_class($field->fieldType()));
	}
}