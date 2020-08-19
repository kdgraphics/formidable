<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Forms;
use craft\base\Model;

class Form extends Model
{
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
	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;
	public $uid;

	public function rules()
	{
		return [
			[['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails'], 'string'],
			[['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails'], 'trim'],
			[['id', 'notifyAdmin_id', 'notify_id'], 'integer'],
			[['honeypot', 'saveSubmissions'], 'boolean'],
			['notifyEmails', 'validateEmails'],
			['notifyEmails', 'required', 'when' => function($model){
				return $model->notifyAdmin_id != 0;
			}],
			['notifyField', 'required', 'when' => function($model){
				return $model->notify_id != 0;
			}],
			['honeypotField', 'required', 'when' => function($model){
				return $model->honeypot;
			}],
			[['name', 'handle'], 'required'],
			[['notify_id', 'notifyAdmin_id'], 'default', 'value' => null, 'when' => function($model,$attribute){
				return empty($model->$attribute);
			}]
		];
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

	public function getFields()
	{
		return Forms::$plugin->fields->getFormFields($this);
	}
}