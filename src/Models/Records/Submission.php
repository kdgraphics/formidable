<?php 

namespace Inspire\Formidable\Models\Records;

use Inspire\Formidable\Models\Submission as SubmissionModel;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

class Submission extends ActiveRecord
{	
	use SoftDeleteTrait;
	
	public static function tableName()
	{
		return '{{%formidable_submissions}}';
	}

	public function toModel()
	{
		return new SubmissionModel($this->getAttributes());
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