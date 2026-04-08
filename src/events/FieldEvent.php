<?php

namespace Inspire\Formidable\events;

use yii\base\Event;

class FieldEvent extends Event
{
    public $field;
    public $isNew;
}
