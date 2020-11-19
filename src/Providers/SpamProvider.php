<?php 

namespace Ryssbowh\Formidable\Providers;

use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\Records\Spam;
use Ryssbowh\Formidable\Models\Spam as SpamModel;
use yii\data\ActiveDataProvider;

class SpamProvider extends ActiveDataProvider
{
    public $trashed;

	public function init()
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