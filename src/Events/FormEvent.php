<?php

namespace Inspire\Formidable\Events;

use yii\base\Event;

class FormEvent extends Event
{
	public $form;
	public $fields;
	public $rules;
	public $isNew;
	public $errors;
	public $oldInput;
}
