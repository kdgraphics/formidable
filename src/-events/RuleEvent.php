<?php

namespace Inspire\Formidable\events;

use yii\base\Event;

class RuleEvent extends Event
{
	public $rule;
	public $isNew;
}
