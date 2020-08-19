<?php

namespace Inspire\Forms\Events;

use yii\base\Event;

class FormEvent extends Event
{
	public $form;
	public $isNew;
}