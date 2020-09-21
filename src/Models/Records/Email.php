<?php 

namespace Ryssbowh\Formidable\Models\Records;

use Ryssbowh\Formidable\Models\Notification as NotificationModel;
use craft\db\ActiveRecord;

class Email extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%formidable_emails}}';
	}
}