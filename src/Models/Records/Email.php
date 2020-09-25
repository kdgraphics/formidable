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

	public function getEmails()
	{
		return json_decode($this->emails, true);
	}

	public function setEmails($emails)
	{
		return json_encode($this->emails);
	}

	public function getSentFrom()
	{
		return json_decode($this->sentFrom, true);
	}

	public function setSentFrom($from)
	{
		return json_encode($this->sentFrom);
	}
}