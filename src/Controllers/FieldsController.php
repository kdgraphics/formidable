<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\FormsAsset;
use Inspire\Forms\Forms;
use Inspire\Forms\Models\Form;
use craft\web\Controller;
use yii\web\BadRequestHttpException;

class FieldsController extends Controller
{
	public function actionCreate(string $handle)
	{
        $this->requirePostRequest();
        $this->requireAcceptsJson();
		$field = Forms::$plugin->fields->getRegistered($handle);
        if (!$field) {
            throw new BadRequestHttpException("handle is missing in the request");
        }
        $html = $this->view->renderTemplate($field->backendTemplate(), [
        	'field' => $field
        ]);
        return $this->asJson([
        	'html' => $html,
        	'friendlyName' => $field->friendlyName()
        ]);
	}
}