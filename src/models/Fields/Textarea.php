<?php

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\models\FormSubmission;

class Textarea extends Text
{
    public $rows;

    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'textarea';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Text area';
    }

    /**
     * inheritDoc
     **/
    public function getExtraCpRules(): array
    {
        $rules = parent::getExtraCpRules();
        $rules[] = ['rows', 'number'];
        return $rules;
    }
}
