<?php 

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FormPosted;

class Text extends Field
{
    public $placeholder;
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
        return 'text';
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
        return 'Text';
    }

    /**
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return ['placeholder', 'label', 'default', 'labelHtml', 'tooltip', 'tooltipLabel'];
    }

    /**
     * inheritDoc
     **/
    public function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['placeholder','label', 'default', 'tooltip', 'tooltipLabel'], 'string'],
            [['placeholder','label', 'default', 'tooltip', 'tooltipLabel'], 'trim'],
            ['labelHtml', 'boolean', 'trueValue' => true, 'falseValue' => false]
        ]);
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormPosted $posted): array
    {
        return [
            [$this->handle, 'string'], 
            [$this->handle, 'trim']
        ];
    }
}