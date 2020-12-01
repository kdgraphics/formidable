<?php 

namespace Ryssbowh\Formidable\Models\Records;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\RuleInterface;
use craft\db\ActiveRecord;

class Rule extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%formidable_rules}}';
	}

	/**
	 * Turn this active record into a model
	 * @return FieldInterface
	 */
	public function toModel(): RuleInterface
	{
		$model = Formidable::$plugin->rules->getRegistered($this->type);
		$model = new $model($this->getAttributes());
		$model->fields = json_decode($this->fields, true);
		$model->messages = json_decode($this->messages ? $this->messages : '{}');
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