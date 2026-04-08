<?php

namespace Inspire\Formidable\models\Records;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\models\SubmissionValue as SubmissionValueModel;
use craft\db\ActiveRecord;

class SubmissionValue extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%formidable_submissions_values}}';
    }

    public function toModel()
    {
        return new SubmissionValueModel($this->getAttributes());
    }
}
