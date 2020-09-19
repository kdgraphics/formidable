<?php

namespace Ryssbowh\Formidable\Events;

use yii\base\Event;

class RegisterConditionTypes extends Event
{
	public $types;
	
	public function __construct(array $default)
	{
		$this->types = $default;
	}
}