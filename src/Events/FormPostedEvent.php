<?php

namespace Inspire\Formidable\Events;

use Inspire\Formidable\Models\Submission;
use Inspire\Formidable\Services\FormsService;
use yii\base\Event;

class FormPostedEvent extends Event
{
	/**
	 * @var FormPosted
	 */
	public $posted;

	/**
	 * @var ?Submission
	 */
	public $submission;

	/**
	 * @var Form
	 */
	public $form;

	/**
	 * @var Spam
	 */
	public $spam;

	/**
	 * @var int|null
	 * @see 
	 * FormsService::STATUS_SPAM
	 * FormsService::STATUS_VALID
	 * FormsService::STATUS_INVALID
	 */
	public $status;
}