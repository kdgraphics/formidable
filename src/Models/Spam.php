<?php

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Exceptions\FormException;
use Ryssbowh\Formidable\Formidable;
use craft\base\Model;

class Spam extends Model
{
    public $id;
    public $form_id;
    public $ip;
    public $notified;
    public $dateCreated;
    public $dateUpdated;
    public $dateDeleted;
    public $uid;
    public $values;
    public $reason;

    protected $_form;

    public function normalizeValues()
    {
        $values = json_decode($this->values, true);
        $out = [];
        foreach ($values as $fieldHandle => $value) {
            try {
                try {
                    $field = $this->form->getField($fieldHandle);
                    $out[$field->name] = $field::getHtmlValue($value);
                } catch (FormException $e) {
                    $out[$fieldHandle] = json_encode($value);
                }
            } catch (FormException $e) {
                $out[$field] = $value;
            }
        }
        return $out;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $array = parent::toArray($fields, $expand, $recursive);
        $array['dateCreated'] = $this->dateCreated->format('d/m/Y H:i');
        $array['form'] = $this->form->toArray(['id', 'name']);
        $array['values'] = $this->normalizeValues();
        return $array;
    }

    public function getForm(): ?Form
    {
        if ($this->_form === null) {
            $this->_form = Formidable::$plugin->forms->getById($this->form_id);
        }
        return $this->_form;
    }

    public function setForm(Form $form)
    {
        $this->_form = $form;
    }
}
