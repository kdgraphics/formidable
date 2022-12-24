<?php 

namespace Inspire\Formidable\Providers;

use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\Records\Spam;
use Inspire\Formidable\Models\Spam as SpamModel;
use yii\data\ActiveDataProvider;

class SpamProvider extends ActiveDataProvider
{
    public $trashed;

	public function init(): void
    {
        parent::init();
        if ($this->trashed === -1) {
            $this->query = Spam::findWithTrashed();
        } elseif ($this->trashed === true) {
            $this->query = Spam::findTrashed();
        } else {
            $this->query = Spam::find();
        }
    }

    public function prepareModels()
    {
        return array_map(function ($record) {
        	return $record->toModel();
        }, parent::prepareModels());
    }
}