<?php 

namespace Inspire\Formidable\Models;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Interfaces\RuleInterface;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormSubmission;
use craft\base\Model;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;

abstract class Rule extends Model implements RuleInterface
{
    public $requiresValue = false;
    public $requiresField = false;

    public $id;
    public $type;
    public $name;
    public $title = '';
    public $conditionName;
    public $message;
    public $messages = [];
    public $value;
    public $form_id;
    public $fields = [];
    public $defaultMessage;
    public $conditions = [];
    public $conditionGroups = [];
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    protected $applies;

    public function init(): void
    {
        $this->type = $this->handle();
        $this->name = $this->name();
        $this->conditionName = $this->conditionName();
        $this->defaultMessage = $this->defaultMessage();
        $this->message = $this->message ?? $this->defaultMessage();
        parent::init();
    }

    public function defineRules(): array
    {
        return [
            [['message', 'value', 'title'], 'string'],
            ['type', function () {
                if (!Formidable::$plugin->rules->getRegistered($this->type)) {
                    $this->addError('type', \Craft::t('formidable', '{type} is not a valid rule type', ['type' => $this->type]));
                }
            }],
            ['fields', 'each', 'rule' => ['string']],
            ['messages', 'each', 'rule' => ['string']]
        ];
    }

    /**
     * @inheritDoc
     */
    public function afterValidate()
    {
        foreach ($this->conditions as $condition) {
            $condition->validate();
        }
        foreach ($this->conditionGroups as $conditionGroup) {
            $conditionGroup->validate();
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
        foreach ($this->conditions as $condition) {
            if ($condition->hasErrors()) {
                return true;
            }
        }
        foreach ($this->conditionGroups as $conditionGroup) {
            if ($conditionGroup->hasErrors()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return array_merge(parent::fields(), ['errors']);
    }

    /**
     * Get rule's form
     * 
     * @return Form
     */
    public function getForm(): Form
    {
        return Formidable::$plugin->forms->getById($this->form_id);
    }

    /**
     * Get rule's fields
     * 
     * @return array
     */
    public function getFields(): array
    {
        $fields = json_decode($this->fields, true);
        return array_filter($this->getForm()->fields, function ($field) use ($fields) {
            return in_array($field->name, $fields);
        });
    }

    /**
     * Project config to be saved
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'title' => $this->title,
            'type' => $this->type,
            'message' => $this->message,
            'messages' => $this->messages,
            'value' => $this->value,
            'fields' => $this->fields,
            'uid' => $this->uid ?? StringHelper::UUID(),
            'conditionGroups' => array_map(function ($group) {
                return $group->getConfig();
            }, $this->conditionGroups),
            'conditions' => array_map(function ($condition) {
                return $condition->getConfig();
            }, $this->conditions),
        ];
    }

    /**
     * @inheritDoc
     */
    public function appliesTo(FormSubmission $submission): bool
    {
        if (!is_null($this->applies)) {
            return $this->applies;
        }
        if (!$this->conditions and !$this->conditionGroups) {
            $this->applies = true;
        } else {
            $conditionnables = array_merge($this->conditions, $this->conditionGroups);
            $result = true;
            foreach ($conditionnables as $conditionnable) {
                $result = $conditionnable->appliesTo($submission, $result);
            }
            $this->applies = $result;
        }
        return $this->applies;
    }

    /**
     * @inheritDoc
     */
    public function validateRule(string $fieldName, FormSubmission $submission)
    {
        if (!$this->appliesTo($submission)) {
            return;
        }
        $field = $submission->getForm()->getField($fieldName);
        if ($this->requiresValue) {
            $result = $this->check($field, $submission->{$fieldName} ?? null, $this->value);
        } else if ($this->requiresField) {
            $result = $this->check($field, $submission->{$fieldName} ?? null, $submission->{$this->value});
        } else {
            $result = $this->check($field, $submission->{$fieldName} ?? null);
        }
        if (!$result) {
            $submission->addError($fieldName, $this->i18Message($field, $submission->{$fieldName}));
        }
    }

    /**
     * Translate and replace variable in message
     * 
     * @param  FieldInterface $field
     * @param  mixed          $value
     * @return string
     */
    public function i18Message(FieldInterface $field, $value): string
    {
        if (isset($this->messages->{$field->handle})) {
            $message = \Craft::t('site', $this->messages->{$field->handle});
        } else {
            $message = \Craft::t('site', $this->message);
        }
        $message = str_replace('{value}', $field::getStringValue($value), $message);
        $matches = [];
        preg_match_all("/{field\.([\w\-_]+)}/", $message, $matches);
        foreach ($matches[1] ?? [] as $match) {
            $message = str_replace("{field.$match}", $field->$match, $message);
        }
        $matches = [];
        preg_match_all("/{rule\.([\w-]+)}/", $message, $matches);
        foreach ($matches[1] ?? [] as $match) {
            $message = str_replace("{rule.$match}", $this->$match, $message);
        }
        return ucfirst($message);
    }

    /**
     * @inheritDoc
     */
    public function frontJs(): string
    {
        return "new formidable.formRule({
            type: '{$this->handle()}',
            value: '{$this->value}',
            message: '".addslashes(\Craft::t('site', $this->message))."',
            messages: JSON.parse('".addslashes(json_encode(array_map(function($message){return \Craft::t('site', $message);}, (array)$this->messages)))."'),
            fields: [".implode(',',array_map(function($field){return "'".$field."'";}, $this->fields))."],
            conditions: [" . implode(',', array_map(function($condition) {return $condition->frontJs();}, $this->conditions)) . "],
            conditionGroups: [" . implode(',', array_map(function($group) {return $group->frontJs();}, $this->conditionGroups)) . "]
        })";
    }
}
