<?php

namespace Inspire\Formidable\Events;

use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\Submission;
use craft\mail\Message;
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