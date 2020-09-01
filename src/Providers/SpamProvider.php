<?php 

namespace Inspire\Forms\Providers;

use Inspire\Forms\Models\Form;
use Inspire\Forms\Models\Records\Spam;
use Inspire\Forms\Models\Spam as SpamModel;
use yii\data\ActiveDataProvider;

class SpamProvider extends ActiveDataProvider
{
	public function init()
    {
        parent::init();
        $this->query = Spam::find()->with('form');
    }

    public function prepareModels()
    {
        return array_map(function ($record) {
        	$model = new SpamModel($record->getAttributes());
        	$model->form = $record->form->toModel();
        	return $model;
        }, parent::prepareModels());
    }
}