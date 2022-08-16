<?php 

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Events\FormEvent;
use Ryssbowh\Formidable\Exceptions\FormException;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\CaptchaInterface;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Models\Notification;
use Ryssbowh\Formidable\Services\FormsService;
use craft\base\Model;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\mail\Message;

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
        return [
            [['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails', 'notifySpamEmails','redirect', 'class', 'method', 'submitLabel', 'color', 'submitClass', 'notifyAdmin_key', 'notify_key', 'notifySpam_key', 'htmlId', 'spamCheck'], 'string'],
            [['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails', 'redirect', 'class', 'submitLabel', 'color', 'submitClass'], 'trim'],
            [['id'], 'integer'],
            [['honeypot', 'saveSubmissions', 'saveSpam', 'frontEndValidation', 'obfuscateFieldNames', 'autocomplete'], 'boolean', 'trueValue' => true, 'falseValue' => false],
            [['notifyEmails', 'notifySpamEmails'], 'validateEmails'],
            ['handle', 'validateHandle'],
            ['method', 'in', 'range' => ['GET', 'POST']],
            ['notifyEmails', 'required', 'when' => function($model){
                return $model->notifyAdmin_key;
            }],
            ['notifySpamEmails', 'required', 'when' => function($model){
                return $model->notifySpam_key;
            }],
            ['captcha', function($attribute){
                if (!in_array($this->$attribute, array_keys($this->getCaptchas()))) {
                    $this->addError($attribute, \Craft::t('formidable', 'Captcha is not valid'));
                }
            }],
            ['notifyField', 'required', 'when' => function($model){
                return $model->notify_key;
            }],
            ['honeypotField', 'required', 'when' => function($model){
                return $model->honeypot;
            }],
            [['name', 'handle', 'method', 'submitLabel'], 'required'],
            [['notify_key', 'notifyAdmin_key'], 'default', 'value' => null, 'when' => function($model,$attribute){
                return empty($model->$attribute);
            }]
        ];
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->submitLabel = $this->submitLabel ? $this->submitLabel : \Craft::t('formidable', 'Submit');
        parent::init();
    }

    /**
     * @inheritDoc
     */
    public function afterValidate()
    {
        foreach ($this->fields as $field) {
            $field->validate();
        }
        foreach ($this->rules as $rule) {
            $rule->validate();
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
        foreach ($this->rules as $rule) {
            if ($rule->hasErrors()) {
                return true;
            }
        }
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
    public function fields()
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
        foreach($emails as $email) {
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
        if (!$template) {
            $template = 'formidable/forms/form';
        }
        $event = new FormEvent([
            'form' => $this,
            'errors' => $this->getSessionErrors(),
            'oldInput' => $this->getOldInput()
        ]);
        $this->trigger(self::EVENT_RENDER, $event);
        $variables = array_merge($variables, [
            'form' => $this,
            'fields' => $this->activeRootFields,
            'errors' => $event->errors,
            'oldInput' => $event->oldInput
        ]);
        return \Craft::$app->getView()->renderTemplate($template, $variables);
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
        return 'formidable_'.str_replace('-', '_', $this->handle);
    }

    /**
     * Front javascript
     * 
     * @return string
     */
    public function getFrontJs(): string
    {
        Formidable::$plugin->forms->enableFrontJs();
        $js = "
document.addEventListener('DOMContentLoaded', (event) => {
    formidable.forms['{$this->generateId()}'] = new formidable.form ({
        id: '{$this->generateId()}',
        rules: [";
        foreach ($this->getRules() as $rule) {
            $js .= $rule->frontJs().",
            ";
        }
        $js .= "],
        fields: {
        ";
        foreach ($this->getActiveFields() as $field) {
            $js .= "'".$field->handle . '\': ' . $field->frontJs($this) . ',
            ';
        }
        $js .= "
        }
    });
});
";
        if ($this->frontEndValidation) {
            $js .= <<<JS
document.addEventListener('DOMContentLoaded', (event) => {
    document.getElementById('{$this->generateId()}').addEventListener('submit', function(e){
        if (!formidable.validateForm(this, e)) {
            e.preventDefault();
        }
    });
});
JS;
        }
        if ($this->honeypot) {
            $js .= <<<JS
document.addEventListener('DOMContentLoaded', (event) => {
    var css = {position: 'absolute', left: '-100000px', overflow: 'hidden'};
    var elem = document.getElementById('{$this->generateId()}').querySelector('[id={$this->handle}-field-{$this->honeypotField}]');
    for (var property in css) {
        elem.style[property] = css[property];
    }
});
JS;
        }
        
        return $js;
    }
}
