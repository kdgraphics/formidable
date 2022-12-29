<?php 

namespace Inspire\Formidable\records;

use Inspire\Formidable\Exceptions\FormException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\Interfaces\FieldInterface;
use craft\db\ActiveRecord;

class FieldRecord extends ActiveRecord
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
		if (!$model) {
			throw FormException::noFieldType($this->type);
		}
		$model = new $model($this->getAttributes());
		return $model;
	}
}