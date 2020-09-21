<?php 

namespace Ryssbowh\Formidable\Exceptions;

use Ryssbowh\Formidable\Models\Form;

class NotificationException extends \Exception
{
	public static function idNotFound($id)
	{
		return new static("Notification $id not found");	
	}
}