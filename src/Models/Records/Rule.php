<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\RuleInterface;
use craft\db\ActiveRecord;

class Rule extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%inspireforms_rules}}';
	}

	/**
	 * Turn this active record into a model
	 * @return FieldInterface
	 */
	public function toModel(): RuleInterface
	{
		$model = Forms::$plugin->rules->getRegistered($this->type);
		$model = new $model($this->getAttributes());
		$model->conditions = array_map(function ($condition) {
			return $condition->toModel();
		}, $this->conditions);
		$model->conditionGroups = array_map(function ($group) {
			return $group->toModel();
		}, $this->conditionGroups);
		return $model;
	}

	/**
	 * Has many relation
	 * 
	 * @return ActiveQuery
	 */
	public function getConditions() 
	{
		return $this->hasMany(Condition::className(), ['rule_id' => 'id']);
	}

	/**
	 * Has many relation
	 * 
	 * @return ActiveQuery
	 */
	public function getConditionGroups()
	{
		return $this->hasMany(ConditionGroup::className(), ['rule_id' => 'id']);
	}
}