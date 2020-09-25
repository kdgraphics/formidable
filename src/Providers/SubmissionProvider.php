<?php 

namespace Ryssbowh\Formidable\Providers;

use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\Records\Submission;
use Ryssbowh\Formidable\Models\Submission as SubmissionModel;
use yii\data\ActiveDataProvider;

class SubmissionProvider extends ActiveDataProvider
{
    public $trashed;

	public function init()
    {
        parent::init();
        if ($this->trashed === -1) {
            $this->query = Submission::findWithTrashed()->with('values');
        } elseif ($this->trashed === true) {
            $this->query = Submission::findTrashed()->with('values');
        } else {
            $this->query = Submission::find()->with('values');
        }
    }

    public function prepareModels()
    {
        return array_map(function ($record) {
        	return $record->toModel();
        }, parent::prepareModels());
    }
}