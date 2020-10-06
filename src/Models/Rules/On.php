<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Exceptions\RuleException;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Models\FieldTypes\BooleanField;
use Ryssbowh\Formidable\Models\Rule;

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
			switch (field.type) {
				case 'string':
					return value.length > against;
			}
			throw 'Field type '+field.type+' can\'t be checked for a bigger than rule';
		}";
	}
}