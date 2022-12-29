<?php 

namespace Inspire\Formidable\records;

use Inspire\Formidable\Models\Spam;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

class SpamRecord extends ActiveRecord
{
	use SoftDeleteTrait;
	
	public static function tableName()
	{
		return '{{%formidable_spams}}';
	}

	public function toModel()
	{
		$spam = new Spam;
		$spam->setAttributes($this->getAttributes());
		return $spam;
	}

	public function getForm()
	{
		return $this->hasOne(FormRecord::className(), ['id' => 'form_id']);
	}
}