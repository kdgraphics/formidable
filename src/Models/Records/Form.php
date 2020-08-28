<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Forms;
use Inspire\Forms\Models\Form as FormModel;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

class Form extends ActiveRecord
{
	use SoftDeleteTrait;

	public static function tableName()
	{
		return '{{%inspireforms_forms}}';
	}

	public function toModel()
	{
		$form = new FormModel($this->getAttributes());
		if ($this->fields) {
			$form->fields = array_map(function($field){
				return $field->toModel();
			}, $this->fields);
		}
		return $form;
	}

	public function getFields()
	{
		return $this->hasMany(Field::className(), ['form_id' => 'id'])->orderBy(['order' => SORT_ASC]);
	}
}