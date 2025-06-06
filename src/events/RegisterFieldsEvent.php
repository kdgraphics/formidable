<?php

namespace Inspire\Formidable\events;

use Inspire\Formidable\interfaces\FieldInterface;
use yii\base\Event;

class RegisterFieldsEvent extends Event
{
	protected $fields = [];

	/**
	 * Registers a field on the event
	 * 
	 * @param FieldInterface $field
	 */
	public function addField(FieldInterface $field)
	{
		$this->fields[$field->handle()] = $field;
	}

	/**
	 * Get all registered fields
	 * 
	 * @return array
	 */
	public function getFields(): array
	{
		return $this->fields;
	}
}
