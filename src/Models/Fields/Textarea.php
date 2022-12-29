<?php 

namespace Inspire\Formidable\Models\Fields;

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
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return array_merge(parent::defineOptions(), ['rows']);
    }

    /**
     * inheritDoc
     **/
    public function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            ['rows', 'number']
        ]);
    }
}