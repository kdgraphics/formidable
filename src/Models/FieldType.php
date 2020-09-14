<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Events\RegisterConditionTypes;
use Inspire\Forms\Events\RegisterRuleTypes;
use Inspire\Forms\Interfaces\FieldTypeInterface;
use craft\base\Model;

abstract class FieldType extends Model implements FieldTypeInterface
{
	protected $defaultConditionTypes = [];

	protected $conditionTypes;

	protected $defaultRuleTypes = [];

	protected $ruleTypes;

	protected function __construct(){}

	public function getValidConditionTypes(): array
	{
		if (is_null($this->conditionTypes)) {
			$event = new RegisterConditionTypes($this->defaultConditionTypes);
            $this->trigger(FieldTypeInterface::EVENT_CONDITION_TYPES, $event); 
            $this->conditionTypes = $event->types;
		}
		return $this->conditionTypes;
	}

	public function getValidRuleTypes(): array
	{
		if (is_null($this->ruleTypes)) {
			$event = new RegisterRuleTypes($this->defaultRuleTypes);
            $this->trigger(FieldTypeInterface::EVENT_RULE_TYPES, $event); 
            $this->ruleTypes = $event->types;
		}
		return $this->ruleTypes;
	}
}