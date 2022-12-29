<?php 

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FormPosted;

class Hidden extends Field
{
    public $default;

    /**
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return ['default'];
    }

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

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormPosted $posted): array
    {
        return [];
    }
}