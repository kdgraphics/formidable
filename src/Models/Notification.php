<?php 

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Records\Submission;
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

	/**
	 * Sends an email for this notification
	 * 
	 * @param  FormSubmission $formSubmission
	 * @param  array          $emails
	 * @param  Submission     $submission
	 * @param  bool|boolean   $forAdmin
	 * @return bool
	 */
	public function send(FormSubmission $formSubmission, array $emails, Submission $submission, bool $forAdmin = false): bool
	{
		return Formidable::$plugin->notifications->send($this, $formSubmission, $submission, $emails, $forAdmin);
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