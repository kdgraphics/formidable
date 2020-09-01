<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Models\Spam as SpamModel;
use craft\db\ActiveRecord;

class Spam extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%inspireforms_spams}}';
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