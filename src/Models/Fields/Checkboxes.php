<?php 

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FieldTypes\ArrayField;
use Inspire\Formidable\traits\FieldOptions;

class Checkboxes extends Field
{
    use FieldOptions {
        FieldOptions::defineRules as defineOptionsRules;
        FieldOptions::defineOptions as defineOptionsOptions;
    }

    protected $fieldTypeClass = ArrayField::class;

    public $label;
    public $labelHtml = false;
    public $tooltip;
    public $tooltipLabel;
    public $optionContainerClass;
    public $optionLabelClass;

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
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return array_merge($this->defineOptionsOptions(), ['label', 'labelHtml', 'tooltip', 'tooltipLabel', 'optionLabelClass', 'optionContainerClass']);
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
                [['label', 'tooltip', 'tooltipLabel', 'optionLabelClass', 'optionContainerClass'], 'string'],
                [['label', 'tooltip', 'tooltipLabel', 'optionLabelClass', 'optionContainerClass'], 'trim'],
                [['labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false],
                [['optionLabelClass', 'optionContainerClass'], 'validateHtmlClasses'],
            ]
        );
    }
}