<?php 

namespace Inspire\Formidable\records;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\SubmissionValue;
use craft\db\ActiveRecord;

class SubmissionValueRecord extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%formidable_submissions_values}}';
	}

	public function toModel()
	{
		return new SubmissionValue($this->getAttributes());
	}
}