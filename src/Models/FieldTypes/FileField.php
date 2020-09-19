<?php 

namespace Ryssbowh\Formidable\Models\FieldTypes;

use Ryssbowh\Formidable\Models\FieldType;

class FileField extends FieldType
{
	private static $instance = null;

	protected $defaultRuleTypes = ['required', 'smallerThan', 'biggerThan', 'size'];

	public static function getInstance()
	{
    	if (self::$instance == null) {
      		self::$instance = new static;
    	}
    	return self::$instance;
  	}
}