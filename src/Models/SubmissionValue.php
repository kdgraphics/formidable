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
	public $friendly;
	public $dateCreated;
	public $dateUpdated;
	public $uid;
}