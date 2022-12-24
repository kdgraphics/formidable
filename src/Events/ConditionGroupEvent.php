<?php

namespace Inspire\Formidable\Events;

use yii\base\Event;

class ConditionGroupEvent extends Event
{
	public $group;
	public $isNew;
}