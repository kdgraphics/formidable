<?php

namespace Ryssbowh\Formidable\Events;

use yii\base\Event;

class FieldEvent extends Event
{
	public $field;
	public $isNew;
}