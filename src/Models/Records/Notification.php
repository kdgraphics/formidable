<?php 

namespace Inspire\Forms\Models\Records;

use Inspire\Forms\Models\Notification as NotificationModel;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

class Notification extends ActiveRecord
{
	use SoftDeleteTrait;
	
	public static function tableName()
	{
		return '{{%inspireforms_notifications}}';
	}

	public function toModel()
	{
		return new NotificationModel($this->getAttributes());
	}
}