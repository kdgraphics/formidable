<?php 

namespace Ryssbowh\Formidable\Interfaces;

interface FieldTypeInterface
{
	const EVENT_RULE_TYPES = 'register_rules';

	public function getValidRuleTypes(): array;
}