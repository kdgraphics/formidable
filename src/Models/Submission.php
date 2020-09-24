<?php 

namespace Ryssbowh\Formidable\Models;

use craft\base\Model;

class Submission extends Model
{
	public $id;
	public $form_id;
	public $read;
	public $notified;
	public $adminNotified;
	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;
	public $form;
	public $values;
	public $uid;
	public $_record;

	public function setAsRead()
	{
		if (!$this->read) {
			$this->read = 1;
			$this->_record->read = 1;
			$this->_record->save();
		}
		return $this;
	}

	public function toArray(array $fields = [], array $expand = [], $recursive = true)
	{
		$array = parent::toArray($fields, $expand, $recursive);
		$array['dateCreated'] = $this->dateCreated->format('d/m/Y H:i');
		return $array;
	}
}