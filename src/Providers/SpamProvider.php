<?php 

namespace Inspire\Formidable\Providers;

use Inspire\Formidable\Models\Form;
use Inspire\Formidable\records\SpamRecord;
use yii\data\ActiveDataProvider;

class SpamProvider extends ActiveDataProvider
{
    public $trashed;

	public function init(): void
    {
        parent::init();
        if ($this->trashed === -1) {
            $this->query = SpamRecord::findWithTrashed();
        } elseif ($this->trashed === true) {
            $this->query = SpamRecord::findTrashed();
        } else {
            $this->query = SpamRecord::find();
        }
    }

    public function prepareModels()
    {
        return array_map(function ($record) {
        	return $record->toModel();
        }, parent::prepareModels());
    }
}