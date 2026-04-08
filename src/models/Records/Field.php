<?php

namespace Inspire\Formidable\models\Records;

use Inspire\Formidable\exceptions\FormException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\interfaces\FieldInterface;
use craft\db\ActiveRecord;

class Field extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%formidable_fields}}';
    }

    /**
     * Turn this active record into a model
     * @return FieldInterface
     */
    public function toModel(): FieldInterface
    {
        $model = Formidable::$plugin->fields->getRegistered($this->type);
        if (!$model) {
            throw FormException::noFieldType($this->type);
        }
        $model = new $model($this->getAttributes());
        return $model;
    }
}
