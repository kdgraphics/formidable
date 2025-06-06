<?php

namespace Inspire\Formidable\events;

use yii\base\Event;

class SpamEvent extends Event
{
	public $form;
	public $spam;
}
