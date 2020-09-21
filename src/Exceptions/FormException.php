<?php 

namespace Ryssbowh\Formidable\Exceptions;

use Ryssbowh\Formidable\Models\Form;

class FormException extends \Exception
{
	public static function idNotFound($id)
	{
		return new static("Form $id not found");	
	}
}