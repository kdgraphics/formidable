<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Models\SubmissionValue as SubmissionValueModel;
use craft\db\ActiveRecord;

class SubmissionValue extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%inspireforms_submissions_values}}';
	}

	public function toModel()
	{
		return new SubmissionValueModel($this->getAttributes());
	}
}