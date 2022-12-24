<?php

namespace Inspire\Formidable\Controllers;

use yii\web\BadRequestHttpException;

class CpFormidableController extends Controller
{
    public function actionIndex()
    {
        if (\Craft::$app->user->checkPermission('manageFormidableSubmissions')) {
            return $this->redirect('formidable/submissions');
        }
        if (\Craft::$app->config->getGeneral()->allowAdminChanges and \Craft::$app->user->checkPermission('manageFormidableForms')) {
            return $this->redirect('formidable/forms');
        }
        return $this->response->setStatusCode(404);
    }
}