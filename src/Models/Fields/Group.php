<?php

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FieldTypes\NullField;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormPosted;

class Group extends Field
{
    protected $fieldTypeClass = NullField::class;

    public $label;
    public $labelHtml = false;
    public $tooltip;
    public $tooltipLabel;

    protected $_fields;

    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'group';
    }

    /**
     * inheritDoc
     **/
    public function getValue()
    {
        return '';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Group';
    }

    /**
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return ['label', 'labelHtml', 'tooltip', 'tooltipLabel'];
    }

    /**
     * @inheritDoc
     */
    public function afterValidate()
    {
        foreach ($this->fields as $field) {
            $field->validate();
        }
        parent::afterValidate();
    }

    /**
     * @inheritDoc
     */
    public function hasErrors($attribute = null): bool
    {
        if (parent::hasErrors($attribute)) {
            return true;
        }
        foreach ($this->fields as $field) {
            if ($field->hasErrors()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Has any of the fields have an error
     * 
     * @param  array   $errors
     * @param  array   $handles
     * @return boolean
     */
    public function hasErrorsInFields(array $errors, ?array $handles = null): bool
    {
        if ($handles == null) {
            $handles = $this->getFieldHandles();
        }
        foreach ($handles as $handle) {
            if (isset($errors[$handle])) {
                return true;
            }
        }
        return false;
    }

    /**
     * inheritDoc
     **/
    public function getFrontEndRules(FormPosted $posted): array
    {
        return [];
    }

    /**
     * Get fields in this group
     * 
     * @return array
     */
    public function getFields(): array
    {
        if ($this->_fields === null) {
            if ($this->id) {
                $this->_fields = Formidable::$plugin->fields->getForGroup($this->id);
            } else {
                $this->_fields = [];
            }
        }
        return $this->_fields;
    }

    /**
     * Get field by handle
     * 
     * @param  string $handle
     * @return ?FieldInterface
     */
    public function getField(string $handle): ?FieldInterface
    {
        foreach ($this->fields as $field) {
            if ($field->handle == $handle) {
                return $field;
            }
        }
        return null;
    }

    /**
     * Get all field handles
     * 
     * @return array
     */
    public function getFieldHandles(): array
    {
        return array_map(function ($field) {
            return $field->handle;
        }, $this->fields);
    }

    /**
     * Set fields in this group
     * 
     * @param ?array $fields
     */
    public function setFields(?array $fields)
    {
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $field->group = $this;
            }
        }
        $this->_fields = $fields;
    }

    /**
     * inheritDoc
     **/
    public function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['label', 'tooltip', 'tooltipLabel'], 'string'],
            [['label', 'tooltip', 'tooltipLabel'], 'trim'],
            [['labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return array_merge(parent::fields(), ['fields', 'errors']);
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return array_merge(parent::getConfig(), [
            'fields' => array_map(function ($field) {
                return $field->getConfig();
            }, $this->fields)
        ]);
    }
}