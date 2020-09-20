<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Exceptions\RuleException;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Models\FieldTypes\ArrayField;
use Ryssbowh\Formidable\Models\FieldTypes\TextField;
use Ryssbowh\Formidable\Models\Rule;

class Contain extends Rule
{
	public $requiresValue = true;

	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Contain');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'contain';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', '{field.name} must contain {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'contains');
	}

	/**
	 * @inheritDoc
	 */
	public function validFieldTypes(): array
	{
		return [ArrayField::class, TextField::class];
	}

	/**
	 * @inheritDoc
	 */
	public function check(FieldInterface $field, $value, $against = null): bool
	{
		switch (get_class($field->fieldType())) {
			case ArrayField::class:
				return in_array($against, $value);
			case TextField::class:
				return (strpos($value, $against) !== false);
		}
		throw RuleException::typeNotValid($rule, get_class($field->fieldType()));
	}
}