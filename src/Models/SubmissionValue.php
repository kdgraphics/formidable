<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Forms;
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
		$field = Forms::$plugin->fields->getRegistered($this->type);
		if (!$field) {
			return $this->value;
		}
		return $field::getSubmissionValue($this->value);
	}
}