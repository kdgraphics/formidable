<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Forms;
use Inspire\Forms\Models\FormSubmission;
use craft\base\Model;

class Notification extends Model
{
	public $id;
	public $name;
	public $method;
	public $template;
	public $html;
	public $sentFrom;
	public $subject;
	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;
	public $uid;

	public function rules()
	{
		return [
			[['name', 'method', 'template', 'html', 'sentFrom', 'subject'], 'string'],
			[['name', 'method', 'template', 'html', 'sentFrom', 'subject'], 'trim'],
			['id', 'integer'],
			['sentFrom', 'email'],
			[['name', 'method', 'sentFrom', 'subject'], 'required'],
			['template', 'required', 'when' => function ($model) {
				return $model->method == 'template';
			}],
			['html', 'required', 'when' => function ($model) {
				return $model->method == 'html';
			}],
			['method', 'in', 'range' => ['template', 'html']]
		];
	}

	public function send(FormSubmission $submission, $email)
	{
		return Forms::$plugin->notifications->send($this, $submission, $email);
	}

	public function getConfig(): array
	{
		return [
            'name' => $this->name,
            'method' => $this->method,
            'template' => $this->template,
            'html' => $this->html,
            'sentFrom' => $this->sentFrom,
            'subject' => $this->subject
        ];
	}
}