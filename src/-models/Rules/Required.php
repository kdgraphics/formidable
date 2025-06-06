<?php 

namespace Inspire\Formidable\models\Rules;

use Inspire\Formidable\exceptions\RuleException;
use Inspire\Formidable\interfaces\FieldInterface;
use Inspire\Formidable\models\FieldTypes\ArrayField;
use Inspire\Formidable\models\FieldTypes\FileField;
use Inspire\Formidable\models\FieldTypes\NumberField;
use Inspire\Formidable\models\FieldTypes\TextField;
use Inspire\Formidable\models\Rule;

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
		throw RuleException::typeNotValid($this, get_class($field->fieldType()));
	}

	/**
	 * inheritDoc
	 */
	public function checkJs(): string
	{
		return "function (field, value, against) {
			switch (field.valueType) {
				case 'text':
				case 'number':
				case 'file':
				case 'array':
					return (value !== null && value.length > 0);
			}
			throw 'Field type '+field.valueType+' can\'t be checked for a required rule';
		}";
	}
}
