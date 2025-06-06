<?php

namespace Inspire\Formidable\models\Records;

use Inspire\Formidable\models\Submission as SubmissionModel;
use Craft;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

class Submission extends ActiveRecord
{
	use SoftDeleteTrait;

	public static function tableName()
	{
		return '{{%formidable_submissions}}';
	}

	public function toModel()
	{
        $return = new SubmissionModel($this->getAttributes());

        if ($return->siteId
            and empty($return->siteName)
            and $site = Craft::$app->getSites()->getSiteById($return->siteId)
        ) {
            $return->siteName = $site->name;
        }

		return $return;
	}

	public function getForm()
	{
		return $this->hasOne(Form::className(), ['id' => 'form_id']);
	}

	public function getDateCreated()
	{
		return $this->dateCreated->format('d/m/Y H:i');
	}
}
