<?php

namespace Inspire\Forms\Events;

use yii\base\Event;

class RegisterRuleTypes extends Event
{
	public $types;
	
	public function __construct(array $default)
	{
		$this->types = $default;
	}
}