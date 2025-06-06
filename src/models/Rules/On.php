<?php

namespace Inspire\Formidable\models\Rules;

use Inspire\Formidable\exceptions\RuleException;
use Inspire\Formidable\interfaces\FieldInterface;
use Inspire\Formidable\models\FieldTypes\BooleanField;
use Inspire\Formidable\models\Rule;

class On extends Rule
{
	/**
	 * @inheritDoc
	 */
	public function name(): string
	{
		return \Craft::t('formidable', 'On');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'on';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} must be on.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'is on');
	}

	/**
	 * @inheritDoc
	 */
	public function validFieldTypes(): array
	{
		return [BooleanField::class];
	}

	/**
	 * @inheritDoc
	 */
	public function check(FieldInterface $field, $value, $against = null): bool
	{
		switch (get_class($field->fieldType())) {
			case BooleanField::class:
				return !empty($value);
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
				case 'boolean':
					return value === true;
			}
			throw 'Field type '+field.valueType+' can\'t be checked for a on rule';
		}";
	}
}
