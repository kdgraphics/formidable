<?php 

namespace Inspire\Forms\Controllers;

use yii\web\BadRequestHttpException;

class CpFieldsController extends Controller
{
	public function actionCreate(string $handle)
	{
        $this->requirePostRequest();
        $this->requireAcceptsJson();
		$field = $this->fields()->getRegistered($handle);
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