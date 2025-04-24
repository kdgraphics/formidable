<?php

namespace Inspire\Formidable\Events;

use yii\base\Event;

class FieldEvent extends Event
{
	public $field;
	public $isNew;
}
