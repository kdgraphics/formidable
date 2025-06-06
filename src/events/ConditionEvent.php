<?php

namespace Inspire\Formidable\events;

use yii\base\Event;

class ConditionEvent extends Event
{
	public $condition;
	public $isNew;
}
