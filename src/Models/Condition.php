<?php 

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\FormSubmission;
use craft\base\Model;

class Condition extends Model
{
	public $id;
	public $conditiongroup_id;
	public $rule_id;
	public $type;
	public $negate;
	public $operator;
	public $value;
	public $field;
	public $dateCreated;
	public $dateUpdated;
	public $uid;

	public function rules()
	{
		return [
			[['type', 'negate', 'operator', 'field', 'type'], 'required'],
			[['type', 'operator', 'field', 'value', 'type'], 'string'],
			['negate', 'boolean'],
			['operator', 'in', 'range' => ['and', 'or']]
		];
	}

	/**
	 * Project config to be saved
	 * 
	 * @return array
	 */
	public function getConfig(): array
	{
		return [
			'type' => $this->type,
			'negate' => $this->negate,
			'operator' => $this->operator,
			'value' => $this->value,
			'field' => $this->field
		];
	}

	public function appliesTo(FormSubmission $submission, bool $result)
	{
		$rule = Formidable::$plugin->rules->getRegistered($this->type);
		$field = $submission->getForm()->getField($this->field);
		if ($rule->requiresValue) {
			$thisResult = $rule->check($field, $submission->{$this->field} ?? null, $this->value);
		} else if ($rule->requiresField) {
			$thisResult = $rule->check($field, $submission->{$this->field} ?? null, $submission->{$this->value});
		} else {
			$thisResult = $rule->check($field, $submission->{$this->field} ?? null);
		}
		if ($this->negate) {
			$thisResult = !$thisResult;
		}
		if ($this->operator == 'and') {
			return ($thisResult and $result);
		}
		return ($thisResult or $result);
	}

	public function frontJs(): string
	{
		return "{...formidable.condition, ...{
			type: '{$this->type}',
			field: '{$this->field}',
			operator: '{$this->operator}',
			negate: '{$this->negate}',
			value: '{$this->value}'
		}}";
	}
}