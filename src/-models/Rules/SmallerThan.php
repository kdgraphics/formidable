<?php

namespace Inspire\Formidable\models\Rules;

use Inspire\Formidable\exceptions\RuleException;
use Inspire\Formidable\interfaces\FieldInterface;
use Inspire\Formidable\models\FieldTypes\ArrayField;
use Inspire\Formidable\models\FieldTypes\FileField;
use Inspire\Formidable\models\FieldTypes\NumberField;
use Inspire\Formidable\models\FieldTypes\TextField;
use Inspire\Formidable\models\Rule;

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
			case ArrayField::class:
				return sizeof($value) < $against;
			case FileField::class:
				return true;
			case TextField::class:
				return strlen($value) < $against;
			case NumberField::class:
				return (int)$value < (int)$against;
		}
		throw RuleException::typeNotValid($rule, get_class($field->fieldType()));
	}

	/**
	 * inheritDoc
	 */
	public function checkJs(): string
	{
		return "function (field, value, against) {
			switch (field.valueType) {
				case 'text':
				case 'array':
					return value.length < against;
				case 'number':
					return parseFloat(value) < parseFloat(against);
			}
			throw 'Field type '+field.valueType+' can\'t be checked for a smaller than rule';
		}";
	}
}
