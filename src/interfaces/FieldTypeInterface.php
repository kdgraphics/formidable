<?php

namespace Inspire\Formidable\interfaces;

interface FieldTypeInterface
{
    const EVENT_RULE_TYPES = 'register_rules';

    public function validRuleTypes(): array;
}
