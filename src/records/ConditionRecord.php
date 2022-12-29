<?php 

namespace Inspire\Formidable\records;

use Inspire\Formidable\Models\Condition;
use craft\db\ActiveRecord;

class ConditionRecord extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%formidable_conditions}}';
	}

	public function toModel()
	{
		return new Condition($this->getAttributes());
	}

	/**
	 * Has one rule relation
	 * 
	 * @return ActiveQuery
	 */
	public function getRule()
	{
		return $this->hasOne(RuleRecord::className(), ['id' => 'rule_id']);
	}

	/**
	 * Has one group relation
	 * 
	 * @return ActiveQuery
	 */
	public function getConditionGroup()
	{
		return $this->hasOne(ConditionGroupRecord::className(), ['id' => 'conditiongroup_id']);
	}
}