<?php 

namespace Inspire\Formidable\Models\Fields;

class Hidden extends Text
{
    public $default;

    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'hidden';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Hidden';
    }
}