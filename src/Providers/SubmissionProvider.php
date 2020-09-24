<?php 

namespace Ryssbowh\Formidable\Providers;

use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\Records\Submission;
use Ryssbowh\Formidable\Models\Submission as SubmissionModel;
use yii\data\ActiveDataProvider;

class SubmissionProvider extends ActiveDataProvider
{
	public function init()
    {
        parent::init();
        $this->query = Submission::find()->with('values');
    }

    public function trashed()
    {
        $this->query = Submission::findTrashed()->with('values');
    }

    public function withTrashed()
    {
        $this->query = Submission::findWithTrashed()->with('values');
    }

    public function prepareModels()
    {
        return array_map(function ($record) {
        	$model = new SubmissionModel($record->getAttributes());
        	$model->form = $record->form->toModel();
            $model->values = $record->values;
        	return $model;
        }, parent::prepareModels());
    }
}