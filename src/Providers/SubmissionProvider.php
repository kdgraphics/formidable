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
        $this->query = Submission::find()->with('values')->with('form');
    }

    public function prepareModels()
    {
        return array_map(function ($record) {
        	$model = new SubmissionModel($record->getAttributes());
        	$model->form = $record->form->toModel();
        	return $model;
        }, parent::prepareModels());
    }
}