<?php 

namespace Inspire\Forms\Models\FieldTypes;

use Inspire\Forms\Models\FieldType;

class BooleanField extends FieldType
{
	private static $instance = null;

	protected $defaultRuleTypes = ['required', 'on', 'off', 'sameThan'];

	public static function getInstance()
	{
    	if (self::$instance == null) {
      		self::$instance = new static;
    	}
    	return self::$instance;
  	}
}