<?php

namespace Inspire\Forms\Events;

use yii\base\Event;

class RegisterConditionTypes extends Event
{
	public $types;
	
	public function __construct(array $default)
	{
		$this->types = $default;
	}
}