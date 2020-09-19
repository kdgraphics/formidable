<?php 

namespace Ryssbowh\Formidable\Models\Records;

use Ryssbowh\Formidable\Models\Notification as NotificationModel;
use craft\db\ActiveRecord;

class Notification extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%formidable_notifications}}';
	}

	public function toModel()
	{
		return new NotificationModel($this->getAttributes());
	}
}