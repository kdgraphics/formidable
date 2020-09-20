<?php 

namespace Ryssbowh\Formidable\Models\Rules;

use Ryssbowh\Formidable\Exceptions\RuleException;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Models\FieldTypes\ArrayField;
use Ryssbowh\Formidable\Models\FieldTypes\FileField;
use Ryssbowh\Formidable\Models\FieldTypes\TextField;
use Ryssbowh\Formidable\Models\Rule;

class Size extends Rule
{
	public $requiresValue = true;

	/**
	 * @inheritDoc
	 */
	public function name(): string 
	{
		return \Craft::t('formidable', 'Size');
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'size';
	}

	/**
	 * @inheritDoc
	 */
	public function defaultMessage(): string
	{
		return \Craft::t('formidable', 'Size of {field.name} must be {rule.value}.');
	}

	/**
	 * @inheritDoc
	 */
	public function conditionName(): string
	{
		return \Craft::t('formidable', 'is of size');
	}

	/**
	 * @inheritDoc
	 */
	public function validFieldTypes(): array
	{
		return [ArrayField::class, FileField::class, TextField::class];
	}

	/**
	 * @inheritDoc
	 */
	public function check(FieldInterface $field, $value, $against = null): bool
	{
		switch (get_class($field->fieldType())) {
			case ArrayField::class:
				return sizeof($value) === sizeof($against);
			case FileField::class:
				return true;
			case TextField::class:
				return strlen($value) === strlen($against);
		}
		throw RuleException::typeNotValid($rule, get_class($field->fieldType()));
	}
}