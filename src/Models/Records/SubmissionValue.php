<?php 

namespace Ryssbowh\Formidable\Models\Records;

use Ryssbowh\Formidable\Models\SubmissionValue as SubmissionValueModel;
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