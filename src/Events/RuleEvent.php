<?php

namespace Ryssbowh\Formidable\Events;

use yii\base\Event;

class RuleEvent extends Event
{
	public $rule;
	public $isNew;
}