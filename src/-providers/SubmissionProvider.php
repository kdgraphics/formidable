<?php 

namespace Inspire\Formidable\providers;

use Inspire\Formidable\models\Form;
use Inspire\Formidable\models\Records\Submission;
use Inspire\Formidable\models\Submission as SubmissionModel;
use yii\data\ActiveDataProvider;

class SubmissionProvider extends ActiveDataProvider
{
    public $trashed;

	public function init(): void
    {
        parent::init();
        if ($this->trashed === -1) {
            $this->query = Submission::findWithTrashed();
        } elseif ($this->trashed === true) {
            $this->query = Submission::findTrashed();
        } else {
            $this->query = Submission::find();
        }
    }

    public function prepareModels()
    {
        return array_map(function ($record) {
        	return $record->toModel();
        }, parent::prepareModels());
    }
}
