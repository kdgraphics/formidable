<?php

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\FormPosted;
use Inspire\Formidable\traits\FieldOptions;

class Radio extends Checkboxes
{
    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'radio';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Radio';
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormPosted $posted): array
    {
        return [
            [$this->handle, 'default', 'value' => []],
            [$this->handle, 'in', 'range' => array_keys($this->availableOptions)]
        ];
    }
}