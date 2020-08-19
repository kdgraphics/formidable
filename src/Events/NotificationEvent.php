<?php

namespace Inspire\Forms\Events;

use yii\base\Event;

class NotificationEvent extends Event
{
	public $notification;
	public $isNew;
}