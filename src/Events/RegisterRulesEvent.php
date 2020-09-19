<?php

namespace Ryssbowh\Formidable\Events;

use Ryssbowh\Formidable\Interfaces\RuleInterface;
use yii\base\Event;

class RegisterRulesEvent extends Event
{
	protected $rules = [];

	/**
	 * Registers a field on the event
	 * 
	 * @param FieldInterface $field
	 */
	public function addRule(RuleInterface $rule)
	{
		$this->rules[$rule->handle()] = $rule;
	}

	/**
	 * Get all registered fields
	 * 
	 * @return array
	 */
	public function getRules(): array
	{
		return $this->rules;
	}
}