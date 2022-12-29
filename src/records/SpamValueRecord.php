<?php 

namespace Inspire\Formidable\records;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\SpamValue;
use craft\db\ActiveRecord;

class SpamValueRecord extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%formidable_spams_values}}';
	}

	public function getForm()
	{
		return $this->hasOne(FormRecord::className(), ['id' => 'form_id']);
	}

	public function toModel()
	{
		return new SpamValue($this->getAttributes());
	}
}