<?php 

namespace Inspire\Formidable\records;

use Inspire\Formidable\Models\ConditionGroup;
use craft\db\ActiveRecord;

class ConditionGroupRecord extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%formidable_conditiongroups}}';
	}

	/**
	 * Has many conditions relation
	 * 
	 * @return ActiveQuery
	 */
	public function getConditions()
	{
		return $this->hasMany(ConditionRecord::className(), ['conditiongroup_id' => 'id']);
	}

	/**
	 * Has one rule relation
	 * 
	 * @return ActiveQuery
	 */
	public function getRule()
	{
		return $this->hasOne(RuleRecord::className(), ['rule_id' => 'id']);
	}

	public function toModel()
	{
		$model = new ConditionGroup($this->getAttributes());
		$model->conditions = array_map(function ($condition) {
			return $condition->toModel();
		}, $this->conditions);
		return $model;
	}
}