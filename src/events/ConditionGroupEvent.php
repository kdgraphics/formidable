<?php

namespace Inspire\Formidable\events;

use yii\base\Event;

class ConditionGroupEvent extends Event
{
	public $group;
	public $isNew;
}
