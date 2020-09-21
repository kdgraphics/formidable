<?php

namespace Ryssbowh\Formidable\Events;

use yii\base\Event;

class EmailEvent extends Event
{
	public $email;
	public $form;
	/**
	 * Entity being submitted, null if submissions are not saved
	 * 
	 * @var Submission|null|Spam
	 */
	public $submitted;
	public $notification;
	/**
	 * Type of email, can be 'user', 'admin' or 'spam'
	 * @var string
	 */
	public $type;
}