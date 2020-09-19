<?php 

namespace Ryssbowh\Formidable\Models\Records;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Form as FormModel;
use craft\db\ActiveRecord;

class Form extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%formidable_forms}}';
	}

	public function toModel()
	{
		$form = new FormModel($this->getAttributes());
		// if ($fields = $this->fields) {
		// 	$form->fields = array_map(function($field){
		// 		return $field->toModel();
		// 	}, $fields);
		// }
		// if ($rules = $this->rules) {
		// 	$form->rules = array_map(function($rule){
		// 		return $rule->toModel();
		// 	}, $rules);
		// }
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