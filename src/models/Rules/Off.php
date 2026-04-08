<?php

namespace Inspire\Formidable\models\Rules;

use Inspire\Formidable\exceptions\RuleException;
use Inspire\Formidable\interfaces\FieldInterface;
use Inspire\Formidable\models\FieldTypes\BooleanField;
use Inspire\Formidable\models\Rule;

class Off extends Rule
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return \Craft::t('formidable', 'Off');
    }

    /**
     * @inheritDoc
     */
    public function handle(): string
    {
        return 'off';
    }

    /**
     * @inheritDoc
     */
    public function defaultMessage(): string
    {
        return \Craft::t('formidable', '{field.name} must be off.');
    }

    /**
     * @inheritDoc
     */
    public function conditionName(): string
    {
        return \Craft::t('formidable', 'is off');
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
                return empty($value);
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
				case 'boolean':
					return value === false;
			}
			throw 'Field type '+field.valueType+' can\'t be checked for a off rule';
		}";
    }
}
