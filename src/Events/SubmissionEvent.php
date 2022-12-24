<?php

namespace Inspire\Formidable\Events;

use yii\base\Event;

class SubmissionEvent extends Event
{
	public $submission;
	public $form;
}