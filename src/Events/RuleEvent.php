<?php

namespace Inspire\Formidable\Events;

use yii\base\Event;

class RuleEvent extends Event
{
	public $rule;
	public $isNew;
}
