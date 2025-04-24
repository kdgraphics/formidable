<?php 

namespace Inspire\Formidable\Models\Records;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\SubmissionValue as SubmissionValueModel;
use craft\db\ActiveRecord;

class SubmissionValue extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%formidable_submissions_values}}';
	}

	public function toModel()
	{
		return new SubmissionValueModel($this->getAttributes());
	}
}
