<?php

namespace Inspire\Forms\Events;

use yii\base\Event;

class SubmissionEvent extends Event
{
	public $submission;
	public $form;
}