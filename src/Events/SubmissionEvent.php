<?php

namespace Ryssbowh\Formidable\Events;

use yii\base\Event;

class SubmissionEvent extends Event
{
	public $submission;
	public $form;
}