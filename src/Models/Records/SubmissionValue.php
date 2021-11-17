<?php 

namespace Ryssbowh\Formidable\Models\Records;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\SubmissionValue as SubmissionValueModel;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

class SubmissionValue extends ActiveRecord
{
	use SoftDeleteTrait;
	
	public static function tableName()
	{
		return '{{%formidable_submissions_values}}';
	}

	public function toModel()
	{
		return new SubmissionValueModel($this->getAttributes());
	}
}