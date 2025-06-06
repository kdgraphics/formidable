<?php

namespace Inspire\Formidable\models\Rules;

use Inspire\Formidable\exceptions\RuleException;
use Inspire\Formidable\interfaces\FieldInterface;
use Inspire\Formidable\models\FieldTypes\NumberField;
use Inspire\Formidable\models\FieldTypes\TextField;
use Inspire\Formidable\models\Rule;

class EqualTo extends Rule
{
	public $requiresValue = true;

	/**
	 * @inheritDoc
	 */
	public function name(): string
	{
		return \Craft::t('formidable', 'Equal to');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'equalTo';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} must equal to {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'is equal to');
	}

	/**
	 * @inheritDoc
	 */
	public function validFieldTypes(): array
	{
		return [TextField::class, NumberField::class];
	}

	/**
	 * @inheritDoc
	 */
	public function check(FieldInterface $field, $value, $against = null): bool
	{
		switch (get_class($field->fieldType())) {
			case TextField::class:
			case NumberField::class:
				return $value === $against;
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
				case 'number':
					return value === against;
			}
			throw 'Field type '+field.valueType+' can\'t be checked for a equal rule';
		}";
	}
}
