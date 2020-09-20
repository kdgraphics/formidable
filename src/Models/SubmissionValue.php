<?php 

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Formidable;
use craft\base\Model;

class SubmissionValue extends Model
{
	public $id;
	public $field;
	public $submission_id;
	public $value;
	public $type;
	public $dateCreated;
	public $dateUpdated;
	public $uid;

	public function getValue()
	{
		$field = Formidable::$plugin->fields->getRegistered($this->type);
		if (!$field) {
			return $this->value;
		}
		return $field::getSubmissionValue($this->value);
	}
}