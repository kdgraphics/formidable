<?php 

namespace Ryssbowh\Formidable\Exceptions;

use Ryssbowh\Formidable\Models\Records\Email;

class ScheduleException extends \Exception
{
	public static function path(string $path)
	{
		return new static("Can't create path $path");
	}

	public static function storageNotFound(Email $email, string $path)
	{
		return new static("Storage file not found : '$path' for email id {$email->id}");
	}

	public static function submissionNotFound(Email$email, $id)
	{
		return new static("Submission with id '$id' not found for email id {$email->id}");
	}

	public static function perms(string $path)
	{
		return new static("Unable to write file $path");
	}
}