<?php 

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FieldTypes\TextField;
use Inspire\Formidable\Models\FormSubmission;

class Select extends Field
{
    public $label;
    public $choices;
    public $default;
    public $labelHtml = false;
    public $multiple = false;
    public $tooltip;
    public $tooltipLabel;

    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'select';
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
        return 'Select';
    }

    /**
     * inheritDoc
     **/
    public function getExtraCpRules(): array
    {
        return [
            [['label', 'default', 'tooltip', 'tooltipLabel'], 'string'],
            [['label', 'default', 'tooltip', 'tooltipLabel'], 'trim'],
            ['choices', 'required'],
            ['choices', 'validateChoices'],
            [['labelHtml', 'multiple'], 'boolean', 'trueValue' => true, 'falseValue' => false]
        ];
    }

    /**
     * Validate the choices
     * 
     * @param string $attribute
     * @param array $param
     * @param $validator
     */
    public function validateChoices($attribute, $param, $validator)
    {
        $choices = $this->$attribute;
        $values = [];
        foreach ($choices as $index => $choice) {
            if ($index != 0 and $choice['value'] == '') {
                $this->addError($attribute, \Craft::t('formidable', 'Value is missing'));
            }
            if ($choice['label'] == '') {
                $this->addError($attribute, \Craft::t('formidable', 'Label is missing'));
            }
            if (in_array($choice['value'], $values)) {
                $value = $choice['value'] ?? '{empty}';
                $this->addError($attribute, \Craft::t('formidable', 'Value {value} is used twice', ['value' => $value]));
            }
            $values[] = $choice['value'];
        }
    }

    /**
     * Build choices as an array 'value' => 'label'
     * @return array
     */
    public function buildChoices(): array
    {
        $choices = [];
        foreach ($this->choices as $choice) {
            $choices[$choice['value']] = $choice['label'];
        }
        return $choices;
    }

    /**
     * Returns all values
     * 
     * @return array
     */
    public function allValues(): array
    {
        $values = [];
        foreach ($this->choices as $choice) {
            $values[] = $choice['value'];
        }
        return $values;
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormSubmission $submission): array
    {
        if ($this->multiple) {
            return [
                [$this->handle, 'each', 'rule' => ['string']],
                [$this->handle, 'each', 'rule' => ['trim']],
                [$this->handle, 'each', 'rule' => ['in', 'range' => $this->allValues()]],
            ];
        }
        return [
            [$this->handle, 'string'],
            [$this->handle, 'trim'],
            [$this->handle, 'in', 'range' => $this->allValues()]
        ];
    }

    /**
     * inheritDoc
     */
    public function afterSubmissionValidated(FormSubmission $submission)
    {
        $value = is_array($submission->{$this->handle}) ? $submission->{$this->handle} : [$submission->{$this->handle}];
        $choices = $this->buildChoices();
        $newValue = [];
        foreach ($value as $value) {
            if ($choices[$value]) {
                $newValue[] = $choices[$value];
            }
        }
        $submission->{$this->handle} = $newValue;
    }

    /**
     * inheritDoc
     */
    public static function getHtmlValue($value): string
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        return implode(', ', $value);
    }

    /**
     * inheritDoc
     */
    public static function getStringValue($value): string
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        return implode(', ', $value);
    }
}