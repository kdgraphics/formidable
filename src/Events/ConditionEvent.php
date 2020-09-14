<?php

namespace Inspire\Forms\Events;

use yii\base\Event;

class ConditionEvent extends Event
{
	public $condition;
	public $isNew;
}