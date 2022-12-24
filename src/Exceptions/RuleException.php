<?php 

namespace Inspire\Formidable\Exceptions;

use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Interfaces\RuleInterface;
use Inspire\Formidable\Models\Form;

class RuleException extends \Exception
{
	public static function typeNotValid(RuleInterface $rule, string $fieldType)
	{
		return new static("Rule {$rule->handle()} is not applicable to field type $fieldType");
	}
}