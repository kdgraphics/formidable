<?php 

namespace Inspire\Formidable\models\Records;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\models\Form as FormModel;
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
