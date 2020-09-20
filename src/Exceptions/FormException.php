<?php 

namespace Ryssbowh\Formidable\Exceptions;

use Ryssbowh\Formidable\Models\Form;

class FormException extends \Exception
{
	public static function noFieldNamed(Form $form, string $name)
	{
		return new static("Form {$form->name} has no field named '$name'");
	}
}