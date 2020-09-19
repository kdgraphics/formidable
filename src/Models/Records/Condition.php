<?php 

namespace Ryssbowh\Formidable\Models\Records;

use Ryssbowh\Formidable\Models\Condition as ConditionModel;
use craft\db\ActiveRecord;

class Condition extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%formidable_conditions}}';
	}

	public function toModel()
	{
		return new ConditionModel($this->getAttributes());
	}

	/**
	 * Has one rule relation
	 * 
	 * @return ActiveQuery
	 */
	public function getRule()
	{
		return $this->hasOne(Rule::className(), ['id' => 'rule_id']);
	}

	/**
	 * Has one group relation
	 * 
	 * @return ActiveQuery
	 */
	public function getConditionGroup()
	{
		return $this->hasOne(ConditionGroup::className(), ['id' => 'conditiongroup_id']);
	}
}