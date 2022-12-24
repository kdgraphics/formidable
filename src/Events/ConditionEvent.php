<?php

namespace Inspire\Formidable\Events;

use yii\base\Event;

class ConditionEvent extends Event
{
	public $condition;
	public $isNew;
}