<?php 

namespace Inspire\Forms\Providers;

use Inspire\Forms\Models\Form;
use Inspire\Forms\Models\Records\Submission;
use Inspire\Forms\Models\Submission as SubmissionModel;
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