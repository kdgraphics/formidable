<?php 

namespace Inspire\Formidable\records;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Interfaces\RuleInterface;
use craft\db\ActiveRecord;
use craft\helpers\Json;

class RuleRecord extends ActiveRecord
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
		$model->fields = Json::decode($this->fields);
		$model->messages = Json::decode($this->messages ? $this->messages : '[]');
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
		return $this->hasMany(ConditionRecord::className(), ['rule_id' => 'id']);
	}

	/**
	 * Has many relation
	 * 
	 * @return ActiveQuery
	 */
	public function getConditionGroups()
	{
		return $this->hasMany(ConditionGroupRecord::className(), ['rule_id' => 'id']);
	}
}