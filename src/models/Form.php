<?php

namespace Inspire\Formidable\models;

use Inspire\Formidable\exceptions\FormException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\interfaces\CaptchaInterface;
use Inspire\Formidable\interfaces\FieldInterface;
use craft\base\Model;

class Form extends Model
{
    const EVENT_RENDER = 'render';
    public $id;
    public $name;
    public $handle;
    public $notify_key;
    public $notifyAdmin_key;
    public $notifySpam_key;
    public $notifyField;
    public $notifyEmails;
    public $notifySpamEmails;
    public $honeypot = true;
    public $htmlId;
    public $honeypotField = 'website';
    public $saveSubmissions = true;
    public $frontEndValidation = true;
    public $saveSpam = true;
    public $obfuscateFieldNames = true;
    public $autocomplete = true;
    public $redirect;
    public $class;
    public $spamCheck;
    public $method = 'POST';
    public $color = '#000000';
    public $captcha;
    public $submitLabel;
    public $submitClass;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    protected $_rootFields;
    protected $_rules;

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        return FormViewHelper::buildRules($this);
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        $this->submitLabel = $this->submitLabel ? $this->submitLabel : \Craft::t('formidable', 'Submit');
        parent::init();
    }

    /**
     * @inheritDoc
     */
    public function afterValidate()
    {
        FormViewHelper::afterValidate($this);
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

        return FormViewHelper::hasNestedErrors($this);
    }

    /**
     * Get the session errors
     *
     * @return array
     */
    public function getSessionErrors(): ?array
    {
        return Formidable::$plugin->forms->getSessionErrors($this->handle);
    }

    /**
     * Get the previous input by user
     *
     * @return array
     */
    public function getOldInput(): ?array
    {
        return Formidable::$plugin->forms->getSessionOldInput($this->handle);
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return array_merge(parent::fields(), ['errors']);
    }

    /**
     * Get form fields, regardless if they are in a group
     *
     * @return array
     */
    public function getFields(): array
    {
        $fields = [];
        foreach ($this->rootFields as $field) {
            $fields[] = $field;
            if ($field->type == 'group') {
                $fields = array_merge($fields, $field->fields);
            }
        }
        return $fields;
    }

    /**
     * Get all active fields
     *
     * @return array
     */
    public function getActiveFields(): array
    {
        return array_values(array_filter($this->fields, function ($field) {
            return $field->active;
        }));
    }

    /**
     * Get root fields (fields not in groups)
     *
     * @return array
     */
    public function getRootFields(): array
    {
        if ($this->_rootFields === null) {
            $this->_rootFields = Formidable::$plugin->fields->getForForm($this);
        }
        return $this->_rootFields;
    }

    /**
     * Get active root fields
     *
     * @return array
     */
    public function getActiveRootFields(): array
    {
        return array_values(array_filter($this->rootFields, function ($field) {
            return $field->active;
        }));
    }

    /**
     * Get all fields by type
     *
     * @param  string $type
     * @return array
     */
    public function getFieldsByType(string $type): array
    {
        return array_values(array_filter($this->fields, function ($field) use ($type) {
            return $field->handle() == $type;
        }));
    }

    /**
     * Set form fields (must be roots only)
     */
    public function setFields(?array $fields)
    {
        $this->_rootFields = $fields;
    }

    /**
     * Get a field by handle
     *
     * @param  string $handle
     * @throws FormException
     * @return FieldInterface
     */
    public function getField(string $handle): FieldInterface
    {
        foreach ($this->fields as $field) {
            if ($field->handle == $handle) {
                return $field;
            }
        }
        throw FormException::noFieldNamed($this, $handle);
    }

    /**
     * Does a field handle exist
     *
     * @param  string  $handle
     * @return boolean
     */
    public function hasField(string $handle): bool
    {
        foreach ($this->fields as $field) {
            if ($field->handle == $handle) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get form rules
     *
     * @return array
     */
    public function getRules(): array
    {
        if ($this->_rules === null) {
            $this->_rules = Formidable::$plugin->rules->getFormRules($this);
        }
        return $this->_rules;
    }

    /**
     * Set form rules
     */
    public function setRules(?array $rules)
    {
        $this->_rules = $rules;
    }

    /**
     * Get available captchas
     *
     * @return array
     */
    public function getCaptchas(): array
    {
        $captchas = [];
        foreach (Formidable::$plugin->captchas->getAllRegistered() as $captcha) {
            $captchas[$captcha->handle()] = $captcha->friendlyName();
        }
        return $captchas;
    }

    /**
     * Get this form captcha
     *
     * @return ?CaptchaInterface
     */
    public function getCaptcha(): ?CaptchaInterface
    {
        return $this->captcha ? Formidable::$plugin->captchas->getRegistered($this->captcha) : null;
    }

    /**
     * Get project config
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'name' => $this->name,
            'handle' => $this->handle,
            'notify_key' => $this->notify_key,
            'notifyAdmin_key' => $this->notifyAdmin_key,
            'notifySpam_key' => $this->notifySpam_key,
            'notifyField' => $this->notifyField,
            'notifyEmails' => $this->notifyEmails,
            'notifySpamEmails' => $this->notifySpamEmails,
            'honeypot' => $this->honeypot,
            'honeypotField' => $this->honeypotField,
            'saveSubmissions' => $this->saveSubmissions,
            'saveSpam' => $this->saveSpam,
            'frontEndValidation' => $this->frontEndValidation,
            'redirect' => $this->redirect,
            'class' => $this->class,
            'spamCheck' => $this->spamCheck,
            'htmlId' => $this->htmlId,
            'submitLabel' => $this->submitLabel,
            'submitClass' => $this->submitClass,
            'method' => $this->method,
            'color' => $this->color,
            'captcha' => $this->captcha,
            'obfuscateFieldNames' => $this->obfuscateFieldNames,
            'fields' => array_map(function ($field) {
                return $field->getConfig();
            }, $this->rootFields),
            'rules' => array_map(function ($rule) {
                return $rule->getConfig();
            }, $this->rules),
        ];
    }

    /**
     * Validate emails
     *
     * @param object $model
     * @param string $attribute
     */
    public function validateEmails($attribute)
    {
        if (!$this->$attribute) {
            return true;
        }
        $emails = array_map('trim', explode(',', trim($this->$attribute)));
        $pass = true;
        foreach ($emails as $email) {
            $isEnv = preg_match('/^\$[A-Za-z-_]+$/', $email);
            if (!$isEnv && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addError($attribute, \Craft::t('formidable', 'Email {email} is not valid', ['email' => $email]));
                $pass = false;
            }
        }
        return $pass;
    }

    /**
     * Obfuscate a field name
     *
     * @param  string $name
     * @return string
     */
    public function obfuscateFieldName(string $name): string
    {
        if ($this->obfuscateFieldNames) {
            return substr(\Craft::$app->security->hashData($name), 0, -1 * strlen($name));
        }
        return $name;
    }

    /**
     * Renders the form (front end)
     *
     * @param  ?string $template
     * @return string
     */
    public function render(?string $template = null, array $variables = [])
    {
        return FormViewHelper::render($this, $template, $variables);
    }

    /**
     * Handle setter, can be used by twig when trying to display two times
     * the same form on the same page, one of them will need to change handle
     *
     * @param string $handle
     */
    public function setHandle(string $handle)
    {
        $this->handle = $handle;
    }

    /**
     * Validate handle
     *
     * @param  Form $model
     * @param  string $attribute
     * @return bool
     */
    public function validateHandle($model, $attribute)
    {
        $exists = Formidable::$plugin->forms->getByHandle($this->handle);
        if ($exists and !$this->id) {
            return false;
        }
        return true;
    }

    /**
     * Generate a html id
     *
     * @return string
     */
    public function generateId(): string
    {
        if ($this->htmlId) {
            return $this->htmlId;
        }
        return 'formidable_' . str_replace('-', '_', $this->handle);
    }

    /**
     * Front javascript
     *
     * @return string
     */
    public function getFrontJs(): string
    {
        return FormViewHelper::buildFrontJs($this);
    }
}
