<?php 

namespace Inspire\Forms\Models\FieldTypes;

use Inspire\Forms\Models\FieldType;

class FileField extends FieldType
{
	private static $instance = null;

	protected $defaultConditionTypes = ['present'];

	protected $defaultRuleTypes = ['required', 'smallerThan'];

	public static function getInstance()
	{
    	if (self::$instance == null) {
      		self::$instance = new static;
    	}
    	return self::$instance;
  	}
}