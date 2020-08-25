<?php 

namespace Inspire\Forms\Services;

use Inspire\Forms\Models\Form;
use Inspire\Forms\Models\Records\Spam;
use Inspire\Forms\Models\Spam as SpamModel;
use craft\base\Component;

class SpamService extends Component
{
	public function create(Form $form): SpamModel
	{
		$spam = new Spam([
			'form_id' => $form->id,
			'ip' => \Craft::$app->request->userIP
		]);
		$spam->save();
		return $spam->toModel();
	}
}