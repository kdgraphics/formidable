<?php 

namespace Inspire\Formidable\Models;

use Inspire\Formidable\Formidable;
use yii\base\DynamicModel;

class FormPosted extends DynamicModel
{
    public $form;
    public $spam_reason;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $rules = [];
        $fieldNames = [];
        foreach ($this->form->getActiveFields() as $field) {
            $rules = array_merge($rules, $field->getFrontEndRules($this));
            $fieldNames[] = $field->handle;
        }
        foreach (Formidable::$plugin->rules->getFormRules($this->form) as $rule) {
            $fields = array_intersect($fieldNames, $rule->fields);
            $rules[] = [$fields, function ($attribute) use ($rule) {
                $rule->validateRule($attribute, $this);
            }, 'skipOnEmpty' => false, 'skipOnError' => false];
        }
        return $rules;
    }
}