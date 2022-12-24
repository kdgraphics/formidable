<?php 

namespace Inspire\Formidable\Exceptions;

use Inspire\Formidable\Models\Form;

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

	public static function noFieldType(string $type)
	{
		return new static("Field type $type doesn't exist");
	}
}