<?php

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\models\Field;
use Inspire\Formidable\models\FieldTypes\BooleanField;
use Inspire\Formidable\models\FormSubmission;

class Checkbox extends Field
{
    protected $fieldTypeClass = BooleanField::class;

    public $label;
    public $default;
    public $labelHtml = false;
    public $tooltip;
    public $tooltipLabel;

    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'checkbox';
    }

    /**
     * inheritDoc
     **/
    public function getValue()
    {
        return $this->_value ?? $this->default;
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Checkbox';
    }

    /**
     * inheritDoc
     **/
    public function getExtraCpRules(): array
    {
        return [
            [['label', 'tooltip', 'tooltipLabel'], 'string'],
            [['label', 'tooltip', 'tooltipLabel'], 'trim'],
            [['default', 'labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false]
        ];
    }

    /**
     * Upload files before validation
     *
     * @param  FormSubmission $submission
     */
    public function beforeSubmissionValidated(FormSubmission $submission)
    {
        $submission->{$this->handle} = $submission->{$this->handle} ? true : false;
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormSubmission $submission): array
    {
        return [[$this->handle, 'boolean']];
    }

    /**
     * @inheritDoc
     */
    public static function getStringValue($value): string
    {
        return $value ? \Craft::t('site', 'Yes') : \Craft::t('site', 'No');
    }

    /**
     * @inheritDoc
     */
    public static function getHtmlValue($value): string
    {
        return self::getStringValue($value);
    }
}
