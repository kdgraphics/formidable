<?php

namespace Ryssbowh\Formidable\Events;

use yii\base\Event;

class ConditionEvent extends Event
{
	public $condition;
	public $isNew;
}