<?php

namespace Inspire\Forms\Events;

use yii\base\Event;

class RuleEvent extends Event
{
	public $rule;
	public $isNew;
}