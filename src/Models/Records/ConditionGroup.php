<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Models\ConditionGroup as ConditionGroupModel;
use craft\db\ActiveRecord;

class ConditionGroup extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%inspireforms_conditiongroups}}';
	}

	/**
	 * Has many conditions relation
	 * 
	 * @return ActiveQuery
	 */
	public function getConditions()
	{
		return $this->hasMany(Condition::className(), ['conditiongroup_id' => 'id']);
	}

	/**
	 * Has one rule relation
	 * 
	 * @return ActiveQuery
	 */
	public function getRule()
	{
		return $this->hasOne(Rule::className(), ['rule_id' => 'id']);
	}

	public function toModel()
	{
		$model = new ConditionGroupModel($this->getAttributes());
		$model->conditions = array_map(function ($condition) {
			return $condition->toModel();
		}, $this->conditions);
		return $model;
	}
}