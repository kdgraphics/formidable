<?php 

namespace Inspire\Formidable\providers;

use Inspire\Formidable\models\Form;
use Inspire\Formidable\models\Records\Spam;
use Inspire\Formidable\models\Spam as SpamModel;
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
