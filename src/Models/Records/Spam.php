<?php 

namespace Ryssbowh\Formidable\Models\Records;

use Ryssbowh\Formidable\Models\Spam as SpamModel;
use craft\db\ActiveRecord;

class Spam extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%formidable_spams}}';
	}

	public function toModel()
	{
		$model = new SpamModel($this->getAttributes());
		$model->form = $this->form->toModel();
		return $model;
	}

	public function getForm()
	{
		return $this->hasOne(Form::className(), ['id' => 'form_id']);
	}
}