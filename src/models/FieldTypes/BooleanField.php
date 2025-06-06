<?php 

namespace Inspire\Formidable\models\FieldTypes;

use Inspire\Formidable\models\FieldType;

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
