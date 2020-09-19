<?php

namespace Ryssbowh\Formidable\Events;

use yii\base\Event;

class NotificationEvent extends Event
{
	public $notification;
	public $isNew;
}