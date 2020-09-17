<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Events\RegisterConditionTypes;
use Inspire\Forms\Events\RegisterRuleTypes;
use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\FieldTypeInterface;
use craft\base\Model;

abstract class FieldType extends Model implements FieldTypeInterface
{
	protected $defaultRuleTypes = [];

	protected $ruleTypes;

	protected function __construct(){}

	public function getValidRuleTypes(): array
	{
		if (is_null($this->ruleTypes)) {
			$event = new RegisterRuleTypes($this->defaultRuleTypes);
            $this->trigger(FieldTypeInterface::EVENT_RULE_TYPES, $event); 
            $this->ruleTypes = array_filter($event->types, function ($type) {
            	return isset(Forms::$plugin->rules->getAllRegistered()[$type]);
            });
		}
		return $this->ruleTypes;
	}
}