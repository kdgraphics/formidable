<?php 

namespace Inspire\Formidable\Models\Rules;

use Inspire\Formidable\Exceptions\RuleException;
use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Models\FieldTypes\ArrayField;
use Inspire\Formidable\Models\FieldTypes\FileField;
use Inspire\Formidable\Models\FieldTypes\NumberField;
use Inspire\Formidable\Models\FieldTypes\TextField;
use Inspire\Formidable\Models\Rule;

class BiggerThan extends Rule
{
	public $requiresValue = true;
	
	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Bigger than');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'biggerThan';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} must be bigger than {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'is bigger than');
	}

	/**
	 * @inheritDoc
	 */
	public function validFieldTypes(): array
	{
		return [ArrayField::class, TextField::class, NumberField::class, FileField::class];
	}

	/**
	 * @inheritDoc
	 */
	public function check(FieldInterface $field, $value, $against = null): bool
	{
		switch (get_class($field->fieldType())) {
			case ArrayField::class:
			case FileField::class:
				return sizeof($value) > $against;
			case TextField::class:
				return strlen($value) > $against;
			case NumberField::class:
				return (int)$value > (int)$against;
		}
		throw RuleException::typeNotValid($this, get_class($field->fieldType()));
	}
}