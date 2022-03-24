<?php

namespace Ryssbowh\Formidable\Models;

use craft\base\Model;

class TemplateForm extends Model
{
    public $form;
    public $template;
    public $variables = [];

    public function render(string $template = null, array $variables = [])
    {
        $this->template = $template;
        $this->variables = $variables;
        return $this->_toString();
    }

    public function __toString()
    {
        if ($this->form) {
            return $this->form->render($this->template, $this->variables);
        }
        return '';
    }
}