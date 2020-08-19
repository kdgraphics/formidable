<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Models\Form as FormModel;
use craft\db\ActiveRecord;

class Form extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%inspireforms_forms}}';
	}

	public function toModel()
	{
		return new FormModel($this->getAttributes());
	}
}