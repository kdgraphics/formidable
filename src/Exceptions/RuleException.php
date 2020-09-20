<?php 

namespace Ryssbowh\Formidable\Exceptions;

use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Interfaces\RuleInterface;
use Ryssbowh\Formidable\Models\Form;

class RuleException extends \Exception
{
	public static function typeNotValid(RuleInterface $rule, string $fieldType)
	{
		return new static("Rule {$rule->handle()} is not applicable to field type $fieldType");
	}
}