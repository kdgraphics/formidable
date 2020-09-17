<?php 

namespace Inspire\Forms\Interfaces;

interface FieldTypeInterface
{
	const EVENT_RULE_TYPES = 'register_rules';

	public function getValidRuleTypes(): array;
}