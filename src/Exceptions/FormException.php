<?php 

namespace Ryssbowh\Formidable\Exceptions;

use Ryssbowh\Formidable\Models\Form;

class FormException extends \Exception
{
	public static function idNotFound($id)
	{
		return new static("Form $id not found");	
	}

	public static function noFieldNamed(Form $form, string $handle)
	{
		return new static("Field $handle doesn't exist in {$form->name}");	
	}
}