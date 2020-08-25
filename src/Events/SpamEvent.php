<?php

namespace Inspire\Forms\Events;

use yii\base\Event;

class SpamEvent extends Event
{
	public $form;
	public $spam;
}