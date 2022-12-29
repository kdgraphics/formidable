<?php 

namespace Inspire\Formidable\Models\Rules;

use Inspire\Formidable\Exceptions\RuleException;
use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Models\FieldTypes\ArrayField;
use Inspire\Formidable\Models\FieldTypes\FileField;
use Inspire\Formidable\Models\FieldTypes\NumberField;
use Inspire\Formidable\Models\FieldTypes\TextField;
use Inspire\Formidable\Models\Rule;
use craft\web\UploadedFile;

class SmallerThan extends Rule
{
	public $requiresValue = true;

	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Smaller than');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'smallerThan';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} must be smaller than {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'is smaller than');
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
			case FileField::class:
			case ArrayField::class:
				return sizeof($value) < $against;
			case TextField::class:
				return strlen($value) < $against;
			case NumberField::class:
				return (int)$value < (int)$against;
		}
		throw RuleException::typeNotValid($this, get_class($field->fieldType()));
	}
}