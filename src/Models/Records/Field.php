<?php 

namespace Ryssbowh\Formidable\Models\Records;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use craft\db\ActiveRecord;

class Field extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%formidable_fields}}';
	}

	/**
	 * Turn this active record into a model
	 * @return FieldInterface
	 */
	public function toModel(): FieldInterface
	{
		$model = Formidable::$plugin->fields->getRegistered($this->type);
		$model = new $model($this->getAttributes());
		return $model;
	}
}