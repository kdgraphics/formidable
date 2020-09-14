<?php 

namespace Inspire\Forms\Interfaces;

interface FieldTypeInterface
{
	const EVENT_CONDITION_TYPES = 'register_conditions';

	const EVENT_RULE_TYPES = 'register_rules';

	public function getValidConditionTypes(): array;

	public function getValidRuleTypes(): array;
}