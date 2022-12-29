<?php

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FieldTypes\NumberField;
use Inspire\Formidable\Models\FormPosted;

class Number extends Text
{
    protected $fieldTypeClass = NumberField::class;

    public $step;
    public $min;
    public $max;

    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'number'; 
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Number';
    }

    /**
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return array_merge(parent::defineOptions(), ['step', 'min', 'max']);
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormPosted $posted): array
    {
        $rules = [[$this->handle, 'number']];
        if ($this->min !== null and $this->min !== '') {
            $rules[0]['min'] = $this->min;
        }
        if ($this->max !== null and $this->max !== '') {
            $rules[0]['max'] = $this->max;
        }
        return $rules;
    }

    /**
     * inheritDoc
     */
    public function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['step', 'min', 'max'], 'number']
        ]);
    }
}