<?php

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\models\FieldTypes\ArrayField;
use Inspire\Formidable\models\FormSubmission;

class Checkboxes extends Radio
{
    protected $fieldTypeClass = ArrayField::class;
    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'checkboxes';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Checkboxes';
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormSubmission $submission): array
    {
        return [
            [$this->handle, function ($attribute, $params, $validator) use ($submission) {
                if (!is_array($submission->$attribute)) {
                    $this->addError($attribute, \Craft::t('formidable', 'Value is invalid'));
                }
            }],
            [$this->handle, 'default', 'value' => []],
            [$this->handle, 'each', 'rule' => ['in', 'range' => $this->allValues()]]
        ];
    }

    /**
     * inheritDoc
     */
    public function afterSubmissionValidated(FormSubmission $submission)
    {
        $rawValue = $submission->{$this->handle};
        if (!$rawValue) {
            return;
        }
        $choices = $this->buildChoices();
        $selected = [];
        foreach ($rawValue as $value) {
            $selected[] = $choices[$value];
        }
        $submission->{$this->handle} = $selected;
    }

    /**
     * @inheritDoc
     */
    public static function getStringValue($value): string
    {
        return is_array($value) ? implode(', ', $value) : (string)$value;
    }
}
