<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Events\FormEvent;
use Inspire\Forms\Forms;
use Inspire\Forms\Models\Notification;
use Inspire\Forms\Services\FormsService;
use craft\base\Model;

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
	public $redirect;
	public $class;
	public $method;
	public $submitLabel;
	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;
	public $uid;
	public $fields = [];

	public function rules()
	{
		return [
			[['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails','redirect', 'class', 'method', 'submitLabel'], 'string'],
			[['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails', 'redirect', 'class', 'submitLabel'], 'trim'],
			[['id', 'notifyAdmin_id', 'notify_id'], 'integer'],
			[['honeypot', 'saveSubmissions'], 'boolean'],
			['notifyEmails', 'validateEmails'],
			['handle', 'validateHandle'],
			['method', 'in', 'range' => ['GET', 'POST']],
			['notifyEmails', 'required', 'when' => function($model){
				return $model->notifyAdmin_id != 0;
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

	public function getConfig(): array
	{
		$config = [
			'name' => $this->name,
            'handle' => $this->handle,
            'notify' => $this->notify_id ? $this->getNotify()->uid : null,
            'notifyAdmin' => $this->notifyAdmin_id ? $this->getAdminNotify()->uid : null,
            'notifyField' => $this->notifyField,
            'notifyEmails' => $this->notifyEmails,
            'honeypot' => $this->honeypot,
            'honeypotField' => $this->honeypotField,
            'saveSubmissions' => $this->saveSubmissions,
            'redirect' => $this->redirect,
            'class' => $this->class,
            'submitLabel' => $this->submitLabel,
            'method' => $this->method,
            'fields' => []
		];
		foreach ($this->fields as $field) {
			$config['fields'][] = $field->getConfig();
		}
		return $config;
	}

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

	public function render($template = null)
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

	public function validateHandle($model, $attribute)
	{
		$exists = Forms::$plugin->forms->getByHandle($this->handle);
		if ($exists and !$this->id) {
			return false;
		}
		return true;
	}

	public function getNotify(): ?Notification
	{
		if (!$this->notify_id) {
			return null;
		}
		return Forms::$plugin->notifications->getById($this->notify_id);
	}

	public function getAdminNotify(): ?Notification
	{
		if (!$this->notifyAdmin_id) {
			return null;
		}
		return Forms::$plugin->notifications->getById($this->notifyAdmin_id);
	}
}