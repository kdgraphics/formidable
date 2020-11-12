<?php 

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Exceptions\FormException;
use Ryssbowh\Formidable\Formidable;
use craft\base\Model;

class Spam extends Model
{
	public $id;
	public $form_id;
	public $ip;
	public $notified;
	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;
	public $uid;
	public $values;
	public $form;
	public $reason;

	public function normalizeValues()
	{
		$values = json_decode($this->values, true);
		$out = [];
		foreach ($values as $field => $value) {
			try {
				$instance = $this->form->getField($field);
				$out[$field] = $instance::getSubmissionValue($value);
			} catch (FormException $e) {
				$out[$field] = $value;
			}
		}
		return $out;
	}

	public function toArray(array $fields = [], array $expand = [], $recursive = true)
	{
		$array = parent::toArray($fields, $expand, $recursive);
		$array['dateCreated'] = $this->dateCreated->format('d/m/Y');
		$array['values'] = $this->normalizeValues();
		return $array;
	}
}