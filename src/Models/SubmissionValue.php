<?php 

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use craft\base\Model;

class SubmissionValue extends Model
{
	public $id;
	public $field_id;
	public $submission_id;
	public $value;
	public $type;
	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;
	public $uid;

	protected $_field = false;

	/**
	 * @inheritDoc
	 */
	public function defineRules(): array
	{
		return [
			[['id', 'field_id', 'submission_id', 'value', 'type', 'dateUpdated', 'dateCreated', 'uid'], 'safe']
		];
	}

	/**
	 * Get field associated to this value
	 * 
	 * @return ?FieldInterface
	 */
	public function getField(): ?FieldInterface
	{
		if ($this->_field === false) {
			$this->_field = null;
			if ($this->field_id) {
				$this->_field = Formidable::$plugin->fields->getById($this->field_id);
			}
		}
		return $this->_field;
	}

	/**
	 * Get a value for a specific style
	 * 
	 * @param  string $style
	 * @return mixed
	 */
	public function getValue(string $style = 'raw')
	{
		if ($style == 'raw') {
			return $this->rawValue();
		}
		if (!$field = $this->field) {
			if (!$field = Formidable::$plugin->fields->getRegistered($this->type)) {
				return $this->rawValue();
			}
		}
		$method = 'get' . ucfirst($style) . 'Value';
		if (method_exists($field, $method)) {
			return $field->$method($this->rawValue());
		}
		return $this->rawValue();
	}

	/**
	 * Get raw value as saved in database
	 * 
	 * @return mixed
	 */
	public function rawValue()
	{
		return json_decode($this->value, true);
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(array $fields = [], array $expand = [], $recursive = true)
	{
		$array = parent::toArray($fields, $expand, $recursive);
		$array['field'] = $this->field;
		$array['rawValue'] = $this->getValue();
		$array['stringValue'] = $this->getValue('string');
		$array['htmlValue'] = $this->getValue('html');
		return $array;
	}

	public function __toString()
	{
		return $this->rawValue();
	}
}