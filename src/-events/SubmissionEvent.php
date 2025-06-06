<?php

namespace Inspire\Formidable\events;

use yii\base\Event;

class SubmissionEvent extends Event
{
	public $submission;
	public $form;
}
