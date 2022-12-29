<?php 

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Models\FormPosted;

class Email extends Text
{
    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'email';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Email';
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormPosted $posted): array
    {
        $rules = parent::getFrontEndRules($posted);
        $rules[] = [$this->handle, 'email'];
        return $rules;
    }

    /**
     * inheritDoc
     */
    public static function getHtmlValue(?FieldInterface $field, $value, bool $isOwn = true): string
    {
        return '<a href="mailto:'.$value.'">'.$value.'</a>';
    }
}