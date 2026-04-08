<?php

namespace Inspire\Formidable\models;

use Inspire\Formidable\events\FieldEvent;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\interfaces\FieldInterface;
use Inspire\Formidable\interfaces\FieldTypeInterface;
use Inspire\Formidable\models\FieldTypes\TextField;
use Inspire\Formidable\models\Fields\Group;
use Inspire\Formidable\models\FormSubmission;
use craft\base\Model;
use craft\helpers\Json;
use craft\helpers\StringHelper;

abstract class Field extends Model implements FieldInterface
{
    const EVENT_RENDER = 'render';

    protected $fieldTypeClass = TextField::class;

    public $id;
    public $active = true;
    public $name = '';
    public $handle = '';
    public $autocomplete = true;
    public $friendlyName;
    public $order;
    public $fieldClass;
    public $containerClass;
    public $labelClass;
    public $instructions;
    public $form_id;
    public $group_id;
    public $type;
    public $dateCreated;
    public $dateUpdated;
    public $uid;
    public $validRules = [];

    protected $_value;
    protected $_frontName;
    protected $_group;
    protected $options;

    protected $rootAttributes = ['id', 'active', 'name', 'handle', 'order', 'form_id', 'options', 'dateCreated', 'dateUpdated', 'uid', 'fieldClass', 'containerClass', 'labelClass', 'instructions', 'type', 'friendlyName', 'validRules', 'value', 'group_id'];

    /**
     * inheritDoc
     **/
    public function init(): void
    {
        parent::init();
        $this->type = $this->handle();
        $this->friendlyName = $this->friendlyName();
        $this->validRules = $this->fieldType()->validRuleTypes();
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return array_merge(parent::fields(), ['errors']);
    }

    /**
     * inheritDoc
     **/
    public function getGroup(): ?Group
    {
        if ($this->group_id) {
            if ($this->_group === null) {
                $this->_group = Formidable::$plugin->fields->getById($this->group_id);
            }
            return $this->_group;
        }
        return null;
    }

    /**
     * inheritDoc
     **/
    public function setGroup(Group $group)
    {
        $this->_group = $group;
    }

    /**
     * inheritDoc
     **/
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * inheritDoc
     **/
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * inheritDoc
     **/
    public function fieldType(): FieldTypeInterface
    {
        return $this->fieldTypeClass::getInstance();
    }

    /**
     * inheritDoc
     **/
    public function defineRules(): array
    {
        return array_merge($this->getExtraCpRules(), [
            [['id', 'order'], 'integer'],
            [['active', 'autocomplete'], 'boolean', 'trueValue' => true, 'falseValue' => false],
            [['name', 'handle'], 'required'],
            [['name', 'fieldClass', 'containerClass', 'instructions', 'labelClass', 'handle'], 'string'],
            [['name', 'fieldClass', 'containerClass', 'instructions', 'labelClass', 'handle'], 'trim']
        ]);
    }

    /**
     * inheritDoc
     **/
    public function frontendTemplate(): string
    {
        return '_formidable/fields/' . $this->handle();
    }

    /**
     * Get form model
     *
     * @return ?Form
     */
    public function getForm(): ?Form
    {
        return Formidable::$plugin->forms->getById($this->form_id);
    }

    /**
     * Extract all options in object
     *
     * @param string $value
     */
    public function setOptions(string $value)
    {
        $this->options = Json::decode($value, true);
        foreach ($this->options as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Unique id for this field
     *
     * @param  Form $form
     * @return string
     */
    public function generateId(Form $form): string
    {
        return $form->handle . '-field-' . $this->handle;
    }

    /**
     * Rebuild options in json/array
     *
     * @param  bool|boolean $asJson
     * @return string|array
     */
    public function rebuildOptions(bool $asJson = false)
    {
        $keys = array_diff(array_keys($this->attributes), $this->rootAttributes);
        $this->options = [];
        foreach ($keys as $key) {
            $this->options[$key] = $this->$key;
        }
        if (!$asJson) {
            return $this->options;
        }
        return json_encode($this->options);
    }

    /**
     * @inheritDoc
     */
    public function getFrontName(): string
    {
        if ($this->_frontName === null) {
            $this->_frontName = $this->handle;
            if ($this->form->obfuscateFieldNames) {
                $this->_frontName = $this->form->obfuscateFieldName($this->handle);
            }
        }
        return $this->_frontName;
    }

    /**
     * Field project config
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'active' => $this->active,
            'name' => $this->name,
            'handle' => $this->handle,
            'fieldClass' => $this->fieldClass,
            'containerClass' => $this->containerClass,
            'labelClass' => $this->labelClass,
            'instructions' => $this->instructions,
            'type' => $this->handle(),
            'options' => $this->rebuildOptions(true),
            'uid' => $this->uid ?? StringHelper::UUID()
        ];
    }

    /**
     * @inheritDoc
     */
    public function afterSubmissionValidated(FormSubmission $submission)
    {
    }

    /**
     * @inheritDoc
     */
    public function beforeSubmissionValidated(FormSubmission $submission)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getHtmlValue($value): string
    {
        return (string)$value;
    }

    /**
     * @inheritDoc
     */
    public static function getStringValue($value): string
    {
        return (string)$value;
    }

    /**
     * @inheritDoc
     */
    public static function getCsvValue($value): string
    {
        return static::getStringValue($value);
    }

    /**
     * Javascript to define this field
     *
     * @return string
     */
    public function frontJs(Form $form): string
    {
        return "new formidable.field({
            name: '" . addslashes($this->name) . "',
            handle: '{$this->handle}',
            frontName: '{$this->frontName}',
            valueType: '{$this->fieldType()->jsType()}',
            type: '" . $this->handle() . "',
            id: '{$this->generateId($form)}',
            formId: '{$form->generateId()}'
        })";
    }

    /**
     * We had values like [""] in the db that broke the element queries
     *
     * @param mixed $values
     * @return array|null
     */
    public static function validatedIDs($values): ?array
    {
        if (!$values) {
            return null;
        }

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $key => $value) {
            if (!is_numeric($value)) {
                unset($values[$key]);
            }
        }

        return $values;
    }
}
