<?php 

namespace Ryssbowh\Formidable\Models;

use yii\base\DynamicModel;

class FormSubmission extends DynamicModel
{
    protected $_form;
    protected $_rules;
    public $_spam_reason;

    /**
     * Form setter
     * 
     * @param Form $form
     */
    public function setForm(Form $form)
    {
        $this->_form = $form;
    }

    /**
     * Form getter
     * 
     * @return Form $form
     */
    public function getForm(): Form
    {
        return $this->_form;
    }

    /**
     * Rules getter
     * 
     * @param array $rules
     */
    public function setRules(array $rules)
    {
        $this->_rules = $rules;
    }

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        $rules = [];
        foreach ($this->_form->getFields() as $field) {
            $rules = array_merge($rules, $field->getFrontEndRules($this));
        }
        foreach ($this->_rules as $rule) {
            $rules[] = [$rule->fields, 'validateRule', 'params' => ['rule' => $rule], 'skipOnEmpty' => false, 'skipOnError' => false];
        }
        return $rules;
    }

    /**
     * Callback to validate rule
     * 
     * @param  string      $attribute
     * @param  array|null  $params
     */
    public function validateRule(string $attribute, $params)
    {
        $rule = $params['rule'];
        $rule->validateRule($attribute, $this);
    }
}