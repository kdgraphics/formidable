<?php 

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FieldTypes\ArrayField;
use Inspire\Formidable\Models\FormPosted;
use Inspire\Formidable\traits\FieldOptions;

class Select extends Field
{
    use FieldOptions {
        FieldOptions::defineRules as defineOptionsRules;
        FieldOptions::defineOptions as defineOptionsOptions;
    }

    protected $fieldTypeClass = ArrayField::class;

    public $label;
    public $labelHtml = false;
    public $multiple = false;
    public $tooltip;
    public $tooltipLabel;
    public $emptyOptionLabel;

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
    public function friendlyName(): string
    {
        return 'Select';
    }

    /**
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return array_merge($this->defineOptionsOptions(), ['label', 'labelHtml', 'multiple', 'tooltip', 'tooltipLabel', 'emptyOptionLabel']);
    }

    /**
     * inheritDoc
     **/
    public function defineRules(): array
    {
        return array_merge(
            parent::defineRules(),
            $this->defineOptionsRules(),
            [
                [['label', 'tooltip', 'tooltipLabel', 'emptyOptionLabel'], 'string'],
                [['label', 'tooltip', 'tooltipLabel', 'emptyOptionLabel'], 'trim'],
                [['labelHtml', 'multiple'], 'boolean', 'trueValue' => true, 'falseValue' => false]
            ]
        );
    }

    /**
     * inheritDoc
     **/
    public function beforeSubmissionValidated(FormPosted $posted)
    {
        $value = $posted->{$this->handle};
        $posted->{$this->handle} = $value ? (is_array($value) ? $value : [$value]) : [];
    }
}