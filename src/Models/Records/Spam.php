<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Models\Spam as SpamModel;
use craft\db\ActiveRecord;

class Spam extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%inspireforms_spams}}';
	}

	public function toModel()
	{
		return new SpamModel($this->getAttributes());
	}
}