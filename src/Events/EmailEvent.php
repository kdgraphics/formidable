<?php

namespace Ryssbowh\Formidable\Events;

use yii\base\Event;

class EmailEvent extends Event
{	
	/**
	 * @var Message
	 */
	public $email;

	/**
	 * @var Form
	 */
	public $form;

	/**
	 * @var Submission
	 */
	public $submission;

	/**
	 * @var boolean
	 */
	public $isValid = true;
}