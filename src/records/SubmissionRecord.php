<?php 

namespace Inspire\Formidable\records;

use Inspire\Formidable\Models\Submission;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

class SubmissionRecord extends ActiveRecord
{	
	use SoftDeleteTrait;
	
	public static function tableName()
	{
		return '{{%formidable_submissions}}';
	}

	public function toModel()
	{
		$sub = new Submission;
		$sub->setAttributes($this->getAttributes());
		return $sub;
	}

	public function getForm()
	{
		return $this->hasOne(FormRecord::className(), ['id' => 'form_id']);
	}

	public function getDateCreated()
	{
		return $this->dateCreated->format('d/m/Y H:i');
	}
}