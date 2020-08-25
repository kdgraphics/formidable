<?php 

namespace Inspire\Forms\Models;

use craft\base\Model;

class SubmissionValue extends Model
{
	public $id;
	public $field;
	public $submission_id;
	public $value;
	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;
	public $uid;
}