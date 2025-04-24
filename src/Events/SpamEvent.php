<?php

namespace Inspire\Formidable\Events;

use yii\base\Event;

class SpamEvent extends Event
{
	public $form;
	public $spam;
}
