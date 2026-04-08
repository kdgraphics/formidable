<?php

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\models\Field;
use Inspire\Formidable\models\FieldTypes\NumberField;
use Inspire\Formidable\models\FormSubmission;

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
     * inheritDoc
     */
    public function getFrontEndRules(FormSubmission $submission): array
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
    public function getExtraCpRules(): array
    {
        return array_merge(parent::getExtraCpRules(), [[['step', 'min', 'max'], 'number']]);
    }
}
