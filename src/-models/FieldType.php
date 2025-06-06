<?php 

namespace Inspire\Formidable\models;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\interfaces\FieldTypeInterface;
use craft\base\Model;

abstract class FieldType extends Model implements FieldTypeInterface
{
	protected $ruleTypes;

	public function __construct(){}

	public function validRuleTypes(): array
	{
		if (is_null($this->ruleTypes)) {
			$this->ruleTypes = [];
			foreach (Formidable::$plugin->rules->getAllRegistered() as $rule) {
				if (in_array(get_class($this), $rule->validFieldTypes())) {
					$this->ruleTypes[] = $rule->handle();
				}
			}
		}
		return $this->ruleTypes;
	}
}
