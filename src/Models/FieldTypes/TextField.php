<?php 

namespace Inspire\Forms\Models\FieldTypes;

use Inspire\Forms\Models\FieldType;

class TextField extends FieldType
{
	private static $instance = null;

	protected $defaultRuleTypes = ['required', 'smallerThan', 'biggerThan', 'size', 'equalTo', 'contain'];

	public static function getInstance()
	{
    	if (self::$instance == null) {
      		self::$instance = new static;
    	}
    	return self::$instance;
  	}
}