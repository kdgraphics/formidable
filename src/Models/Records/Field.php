<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Models\Form as FormModel;
use craft\db\ActiveRecord;

class Field extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%inspireforms_fields}}';
	}

	public function toModel()
	{
		$class = Forms::$plugin->fields->getClassMap($this->handle);
		return new $class($this->getAttributes());
	}
}