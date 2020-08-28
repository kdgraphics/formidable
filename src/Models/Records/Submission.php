<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Models\Submission as SubmissionModel;
use craft\db\ActiveRecord;

class Submission extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%inspireforms_submissions}}';
	}

	public function toModel()
	{
		$model = new SubmissionModel($this->getAttributes());
		$model->form = $this->form->toModel();
		$model->values = array_map(function($value){
			return $value->toModel();
		}, $this->values);
		$model->_record = $this;
		return $model;
	}

	public function getValues()
	{
		return $this->hasMany(SubmissionValue::className(), ['submission_id' => 'id']);
	}

	public function getForm()
	{
		return $this->hasOne(Form::className(), ['id' => 'form_id']);
	}

	public function getDateCreated()
	{
		return $this->dateCreated->format('d/m/Y H:i');
	}
}