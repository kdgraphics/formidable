<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\FieldInterface;
use craft\db\ActiveRecord;

class Field extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%inspireforms_fields}}';
	}

	/**
	 * Turn this active record into a model
	 * @return FieldInterface
	 */
	public function toModel(): FieldInterface
	{
		$model = Forms::$plugin->fields->getRegistered($this->type);
		$model = new $model($this->getAttributes());
		return $model;
	}
}