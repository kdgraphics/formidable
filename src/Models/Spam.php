<?php 

namespace Ryssbowh\Formidable\Models;

use craft\base\Model;

class Spam extends Model
{
	public $id;
	public $form_id;
	public $ip;
	public $notified;
	public $dateCreated;
	public $dateUpdated;
	public $uid;
	public $values;
	public $form;

	public function toArray(array $fields = [], array $expand = [], $recursive = true)
	{
		$array = parent::toArray($fields, $expand, $recursive);
		$array['dateCreated'] = $this->dateCreated->format('d/m/Y');
		return $array;
	}
}