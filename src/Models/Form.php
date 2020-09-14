<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Events\FormEvent;
use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\CaptchaInterface;
use Inspire\Forms\Models\Notification;
use Inspire\Forms\Services\FormsService;
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
	public $notifyField;
	public $notifyEmails;
	public $honeypot;
	public $honeypotField;
	public $saveSubmissions;
	public $saveSpam;
	public $redirect;
	public $class;
	public $method = 'POST';
	public $color;
	public $captcha;
	public $submitLabel;
	public $dateCreated;
	public $dateUpdated;
	public $uid;
	public $fields = [];
	public $rules = [];

	public function rules()
	{
		return [
			[['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails','redirect', 'class', 'method', 'submitLabel', 'color'], 'string'],
			[['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails', 'redirect', 'class', 'submitLabel', 'color'], 'trim'],
			[['id', 'notifyAdmin_id', 'notify_id'], 'integer'],
			[['honeypot', 'saveSubmissions', 'saveSpam'], 'boolean'],
			['notifyEmails', 'validateEmails'],
			['handle', 'validateHandle'],
			['method', 'in', 'range' => ['GET', 'POST']],
			['notifyEmails', 'required', 'when' => function($model){
				return $model->notifyAdmin_id != 0;
			}],
			['captcha', function($attribute){
				if (!in_array($this->$attribute, array_keys($this->getCaptchas()))) {
					$this->addError($attribute, \Craft::t('inspireforms', 'Captcha is not valid'));
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

	/**
	 * Get available captchas
	 * 
	 * @return array
	 */
	public function getCaptchas(): array
	{
		$captchas = [];
		foreach (Forms::$plugin->captchas->getAllRegistered() as $captcha) {
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
		return $this->captcha ? Forms::$plugin->captchas->getRegistered($this->captcha) : null;
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
            'notifyField' => $this->notifyField,
            'notifyEmails' => $this->notifyEmails,
            'honeypot' => $this->honeypot,
            'honeypotField' => $this->honeypotField,
            'saveSubmissions' => $this->saveSubmissions,
            'saveSpam' => $this->saveSpam,
            'redirect' => $this->redirect,
            'class' => $this->class,
            'submitLabel' => $this->submitLabel,
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
	public function validateEmails($model, $attribute)
	{
		if (!$this->notifyEmails) {
			return true;
		}
		$emails = array_map('trim', explode(',', $this->notifyEmails));
		$pass = true;
		foreach($emails as $email) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->addError('notifyEmails', \Craft::t('Email {email} is not valid', ['email' => $email]));
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
			$template = 'inspireforms/forms/form';
		}
		$event = new FormEvent([
            'form' => $this,
            'errors' => \Craft::$app->session->getFlash(FormsService::SESSION_ERRORS_KEY),
            'oldInput' => \Craft::$app->session->getFlash(FormsService::SESSION_OLDINPUT_KEY)
        ]);
		$this->trigger(self::EVENT_RENDER, $event);
		return \Craft::$app->getView()->renderTemplate($template, [
			'form' => $this,
			'fields' => $this->fields,
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
		$exists = Forms::$plugin->forms->getByHandle($this->handle);
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
		return Forms::$plugin->notifications->getById($this->notify_id);
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
		return Forms::$plugin->notifications->getById($this->notifyAdmin_id);
	}
}