<?php 

namespace Inspire\Formidable\Models;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Interfaces\FieldInterface;
use craft\base\Model;
use craft\helpers\Json;

class SpamValue extends Model
{
	public $id;
	public $field_id;
	public $spam_id;
	public $value;
	public $type;
	public $dateCreated;
	public $dateUpdated;
	public $uid;

	protected $_field = false;

	/**
	 * @inheritDoc
	 */
	public function defineRules(): array
	{
		return [
			[['id', 'field_id', 'spam_id', 'value', 'type', 'dateUpdated', 'dateCreated', 'uid'], 'safe']
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
	 * Get a string representation of value
	 * 
	 * @return string
	 */
	public function asString(): string
	{
		if ($field = $this->field) {
			return $field::getStringValue($field, $this->asRaw());
		}
		if ($this->type and $field = Formidable::$plugin->fields->getRegistered($this->type)) {
			return $field::getStringValue($field, $this->asRaw(), false);
		}
		return $this->value ?: '';
	}

	/**
	 * Get a html representation of value
	 * 
	 * @return string
	 */
	public function asHtml(): string
	{
		if ($field = $this->field) {
			return $field::getHtmlValue($field, $this->asRaw());
		}
		if ($this->type and $field = Formidable::$plugin->fields->getRegistered($this->type)) {
			return $field::getHtmlValue($field, $this->asRaw(), false);
		}
		return $this->value ?: '';
	}

	/**
	 * Get raw value as saved in database (decoded)
	 * 
	 * @return mixed
	 */
	public function asRaw()
	{
		return Json::decode($this->value);
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(array $fields = [], array $expand = [], $recursive = true)
	{
		$array = parent::toArray($fields, $expand, $recursive);
		$array['field'] = $this->field;
		$array['rawValue'] = $this->asRaw();
		$array['stringValue'] = $this->asString();
		$array['htmlValue'] = $this->asHtml();
		return $array;
	}

	/**
	 * Get a string representation of the value
	 * @return string
	 */
	public function __toString()
	{
		return $this->asString();
	}
}