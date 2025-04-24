<?php

namespace Inspire\Formidable\Models\Records;

use Inspire\Formidable\Models\Spam as SpamModel;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

class Spam extends ActiveRecord
{
	use SoftDeleteTrait;

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
