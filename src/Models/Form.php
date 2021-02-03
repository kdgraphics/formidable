<?php 

namespace Ryssbowh\Formidable\Models;

use JSMin\JSMin;
use Ryssbowh\Formidable\Events\FormEvent;
use Ryssbowh\Formidable\Exceptions\FormException;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\CaptchaInterface;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Models\Notification;
use Ryssbowh\Formidable\Services\FormsService;
use craft\base\Model;
use craft\helpers\ArrayHelper;

class Form extends Model
{
	const EVENT_RENDER = 'render';
	public $id;
	public $name;
	public $handle;
	public $notify_id;
	public $notifyAdmin_id;
	public $notifySpam_id;
	public $notifyField;
	public $notifyEmails;
	public $notifySpamEmails;
	public $honeypot = 1;
	public $honeypotField;
	public $saveSubmissions = 1;
	public $frontEndValidation = 0;
	public $saveSpam = 1;
	public $redirect;
	public $class;
	public $method = 'POST';
	public $color = '#000000';
	public $captcha;
	public $submitLabel;
	public $submitClass;
	public $dateCreated;
	public $dateUpdated;
	public $uid;
	public $fields;
	public $rules;

	public function rules()
	{
		return [
			[['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails', 'notifySpamEmails','redirect', 'class', 'method', 'submitLabel', 'color', 'submitClass'], 'string'],
			[['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails', 'redirect', 'class', 'submitLabel', 'color', 'submitClass'], 'trim'],
			[['id', 'notifyAdmin_id', 'notify_id', 'notifySpam_id'], 'integer'],
			[['honeypot', 'saveSubmissions', 'saveSpam', 'frontEndValidation'], 'boolean'],
			[['notifyEmails', 'notifySpamEmails'], 'validateEmails'],
			['handle', 'validateHandle'],
			['method', 'in', 'range' => ['GET', 'POST']],
			['notifyEmails', 'required', 'when' => function($model){
				return $model->notifyAdmin_id != 0;
			}],
			['notifySpamEmails', 'required', 'when' => function($model){
				return $model->notifySpam_id != 0;
			}],
			['captcha', function($attribute){
				if (!in_array($this->$attribute, array_keys($this->getCaptchas()))) {
					$this->addError($attribute, \Craft::t('formidable', 'Captcha is not valid'));
				}
			}],
			['notifyField', 'required', 'when' => function($model){
				return $model->notify_id != 0;
			}],
			['honeypotField', 'required', 'when' => function($model){
				return $model->honeypot;
			}],
			[['name', 'handle', 'method', 'submitLabel'], 'required'],
			[['notify_id', 'notifyAdmin_id'], 'default', 'value' => null, 'when' => function($model,$attribute){
				return empty($model->$attribute);
			}]
		];
	}

	public function init()
	{
		$this->submitLabel = $this->submitLabel ? $this->submitLabel : \Craft::t('formidable', 'Submit');
		parent::init();
	}

	/**
	 * Get form fields
	 * 
	 * @return array
	 */
	public function getFields(): array
	{
		if ($this->fields == null) {
			$this->fields = Formidable::$plugin->fields->getFormFields($this);
		}
		return $this->fields;
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
		foreach ($this->getFields() as $field) {
			if ($field->handle == $handle) {
				return $field;
			}
		}
		throw FormException::noFieldNamed($this, $handle);
	}

	/**
	 * Get form rules
	 * 
	 * @return array
	 */
	public function getRules(): array
	{
		if ($this->rules == null) {
			$this->rules = Formidable::$plugin->rules->getFormRules($this);
		}
		return $this->rules;
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
            'notify' => $this->notify_id ? $this->getNotify()->uid : null,
            'notifyAdmin' => $this->notifyAdmin_id ? $this->getAdminNotify()->uid : null,
            'notifySpam' => $this->notifySpam_id ? $this->getSpamNotify()->uid : null,
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
            'submitLabel' => $this->submitLabel,
            'submitClass' => $this->submitClass,
            'method' => $this->method,
            'color' => $this->color,
            'captcha' => $this->captcha
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
		$emails = array_map('trim', explode(',', $this->$attribute));
		$pass = true;
		foreach($emails as $email) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->addError($attribute, \Craft::t('Email {email} is not valid', ['email' => $email]));
				$pass = false;
			}
		}
		return $pass;
	}

	/**
	 * Renders the form (front end)
	 * 
	 * @param ?string $template
	 * @return string
	 */
	public function render(?string $template = null)
	{
		if (!$template) {
			$template = 'formidable/forms/form';
		}
		$event = new FormEvent([
            'form' => $this,
            'errors' => Formidable::$plugin->forms->getSessionErrors($this->handle),
            'oldInput' => Formidable::$plugin->forms->getSessionOldInput($this->handle)
        ]);
		$this->trigger(self::EVENT_RENDER, $event);
		return \Craft::$app->getView()->renderTemplate($template, [
			'form' => $this,
			'fields' => $this->getFields(),
			'errors' => $event->errors,
			'oldInput' => $event->oldInput
		]);
	}

	/**
	 * Validate handle 
	 * 
	 * @param Form $model
	 * @param string $attribute
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
	 * Get user notification
	 * 
	 * @return ?Notification
	 */
	public function getNotify(): ?Notification
	{
		if (!$this->notify_id) {
			return null;
		}
		return Formidable::$plugin->notifications->getById($this->notify_id);
	}

	/**
	 * Get spam notification
	 * 
	 * @return ?Notification
	 */
	public function getSpamNotify(): ?Notification
	{
		if (!$this->notifySpam_id) {
			return null;
		}
		return Formidable::$plugin->notifications->getById($this->notifySpam_id);
	}

	/**
	 * Get admin notification
	 * 
	 * @return ?Notification
	 */
	public function getAdminNotify(): ?Notification
	{
		if (!$this->notifyAdmin_id) {
			return null;
		}
		return Formidable::$plugin->notifications->getById($this->notifyAdmin_id);
	}

	/**
	 * Generate a html id
	 * 
	 * @return string
	 */
	public function generateId(): string
	{
		return 'formidable-'.$this->handle.$this->id;
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
		foreach ($this->getFields() as $field) {
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
	document.getElementById('{$this->generateId()}').querySelector('[id={$this->handle}-field-{$this->honeypotField}]').parentElement.style.display = 'none';
});
JS;
		}
		
		return getenv('ENVIRONMENT') == 'production' ? JSMin::minify($js) : $js;
	}
}