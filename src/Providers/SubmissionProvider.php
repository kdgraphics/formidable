<?php 

namespace Inspire\Formidable\Providers;

use Inspire\Formidable\Models\Form;
use Inspire\Formidable\records\SubmissionRecord;
use yii\data\ActiveDataProvider;

class SubmissionProvider extends ActiveDataProvider
{
    public $trashed;

	public function init(): void
    {
        parent::init();
        if ($this->trashed === -1) {
            $this->query = SubmissionRecord::findWithTrashed();
        } elseif ($this->trashed === true) {
            $this->query = SubmissionRecord::findTrashed();
        } else {
            $this->query = SubmissionRecord::find();
        }
    }

    public function prepareModels()
    {
        return array_map(function ($record) {
        	return $record->toModel();
        }, parent::prepareModels());
    }
}