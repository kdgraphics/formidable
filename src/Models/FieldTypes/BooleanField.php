<?php 

namespace Ryssbowh\Formidable\Models\FieldTypes;

use Ryssbowh\Formidable\Models\FieldType;

class BooleanField extends FieldType
{
	private static $instance = null;

	public static function getInstance()
	{
    	if (self::$instance == null) {
      		self::$instance = new static;
    	}
    	return self::$instance;
  	}

  	public function jsType()
  	{
  		return 'boolean';
  	}
}