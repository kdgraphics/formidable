<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Models\Notification as NotificationModel;
use craft\db\ActiveRecord;

class Notification extends ActiveRecord
{	
	public static function tableName()
	{
		return '{{%inspireforms_notifications}}';
	}

	public function toModel()
	{
		return new NotificationModel($this->getAttributes());
	}
}