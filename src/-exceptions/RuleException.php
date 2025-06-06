<?php 

namespace Inspire\Formidable\exceptions;

use Inspire\Formidable\interfaces\FieldInterface;
use Inspire\Formidable\interfaces\RuleInterface;
use Inspire\Formidable\models\Form;

class RuleException extends \Exception
{
	public static function typeNotValid(RuleInterface $rule, string $fieldType)
	{
		return new static("Rule {$rule->handle()} is not applicable to field type $fieldType");
	}
}
