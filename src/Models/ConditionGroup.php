<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Forms;
use craft\base\Model;
use craft\helpers\ArrayHelper;

class ConditionGroup extends Model
{
	public $id;
	public $rule_id;
	public $conditions;
	public $negate;
	public $operator;
	public $dateCreated;
	public $dateUpdated;
	public $uid;

	public function rules()
	{
		return [
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
			'negate' => $this->negate,
			'operator' => $this->operator
		];
	}
}