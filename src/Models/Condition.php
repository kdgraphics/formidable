<?php 

namespace Ryssbowh\Formidable\Models;

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
			[['type', 'negate', 'operator', 'field'], 'required'],
			[['type', 'operator', 'field', 'value'], 'string'],
			['type', 'in', 'range' => array_keys($this::validTypes())],
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
}