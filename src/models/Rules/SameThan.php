<?php

namespace Inspire\Formidable\models\Rules;

use Inspire\Formidable\exceptions\RuleException;
use Inspire\Formidable\interfaces\FieldInterface;
use Inspire\Formidable\models\FieldTypes\ArrayField;
use Inspire\Formidable\models\FieldTypes\BooleanField;
use Inspire\Formidable\models\FieldTypes\NumberField;
use Inspire\Formidable\models\FieldTypes\TextField;
use Inspire\Formidable\models\Rule;

class SameThan extends Rule
{
    public $requiresValue = false;
    public $requiresField = true;

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return \Craft::t('formidable', 'Same than');
    }

    /**
     * @inheritDoc
     */
    public function handle(): string
    {
        return 'sameThan';
    }

    /**
     * @inheritDoc
     */
    public function defaultMessage(): string
    {
        return \Craft::t('formidable', '{field.name} must be the same than {rule.value}.');
    }

    /**
     * @inheritDoc
     */
    public function conditionName(): string
    {
        return \Craft::t('formidable', 'is same than');
    }

    /**
     * @inheritDoc
     */
    public function validFieldTypes(): array
    {
        return [ArrayField::class, BooleanField::class, TextField::class, NumberField::class];
    }

    /**
     * @inheritDoc
     */
    public function check(FieldInterface $field, $value, $against = null): bool
    {
        switch (get_class($field->fieldType())) {
            case ArrayField::class:
                return empty(array_diff($value, $against));
            case BooleanField::class:
            case TextField::class:
            case NumberField::class:
                return $value === $against;
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
				case 'boolean':
				case 'number':
					return value === against;
				case 'array':
					return formidable.arrayEquals(value, against);
			}
			throw 'Field type '+field.valueType+' can\'t be checked for a same than rule';
		}";
    }
}
