<?php

namespace Inspire\Forms\Events;

use yii\base\Event;

class FieldEvent extends Event
{
	public $field;
	public $isNew;
}