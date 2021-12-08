<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FieldTypes\NullField;
use Ryssbowh\Formidable\Models\FormSubmission;

class Group extends Field
{
    protected $fieldTypeClass = NullField::class;

    public $label;
    public $labelHtml = false;
    public $tooltip;
    public $tooltipLabel;

    protected $_fields;

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
    public function hasErrors($attribute = null)
    {
        if (parent::hasErrors($attribute)) {
            return true;
        }
        foreach ($this->fields as $field) {
            if ($field->hasErrors()) {
                return true;
            }
        }
    }

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
     * inheritDoc
     **/
    public function getFrontEndRules(FormSubmission $submission): array
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
     * Set fields in this group
     * 
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        foreach ($fields as $field) {
            $field->group = $this;
        }
        $this->_fields = $fields;
    }

    /**
     * inheritDoc
     **/
    public function getExtraCpRules(): array
    {
        return [
            [['label', 'tooltip', 'tooltipLabel'], 'string'],
            [['label', 'tooltip', 'tooltipLabel'], 'trim'],
            [['labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getStringValue($value): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public static function getHtmlValue($value): string
    {
        return self::getStringValue($value);
    }

    /**
     * @inheritDoc
     */
    public function fields()
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