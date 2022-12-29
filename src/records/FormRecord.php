<?php 

namespace Inspire\Formidable\records;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\Form;
use craft\db\ActiveRecord;

class FormRecord extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%formidable_forms}}';
	}

	public function toModel()
	{
		$form = new Form($this->getAttributes());
		return $form;
	}

	public function getFields()
	{
		return $this->hasMany(FieldRecord::className(), ['form_id' => 'id'])->orderBy(['order' => SORT_ASC]);
	}

	public function getRules()
	{
		return $this->hasMany(RuleRecord::className(), ['form_id' => 'id']);
	}
}