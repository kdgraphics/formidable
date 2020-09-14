<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Forms;
use Inspire\Forms\Models\Form as FormModel;
use craft\db\ActiveRecord;

class Form extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%inspireforms_forms}}';
	}

	public function toModel()
	{
		$form = new FormModel($this->getAttributes());
		if ($fields = $this->fields) {
			$form->fields = array_map(function($field){
				return $field->toModel();
			}, $fields);
		}
		if ($rules = $this->rules) {
			$form->rules = array_map(function($rule){
				return $rule->toModel();
			}, $rules);
		}
		return $form;
	}

	public function getFields()
	{
		return $this->hasMany(Field::className(), ['form_id' => 'id'])->orderBy(['order' => SORT_ASC]);
	}

	public function getRules()
	{
		return $this->hasMany(Rule::className(), ['form_id' => 'id']);
	}
}