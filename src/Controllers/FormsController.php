<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\FormsAsset;
use Inspire\Forms\Forms;
use Inspire\Forms\Models\Form;
use Inspire\Forms\Models\FormSubmission;
use Inspire\Forms\Services\FormsService;
use craft\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class FormsController extends Controller
{
    public function actionSubmit(int $id)
    {
        $form = $this->forms()->getById($id);
        if (!$form) {
            throw new HttpException(404);
        }
        if ($form->method == 'POST') {
            $this->requirePostRequest();
        }

        $result = $this->forms()->submit($form);

        if ($result === FormsService::INVALID) {
            return $this->onUnvalidForm($form);
        } elseif ($result === FormsService::SPAM) {
            return $this->onSpam($form);
        }
        return $this->onValidSubmission($form);
    }

    protected function onValidSubmission(Form $form)
    {
        $this->session()->getFlash(FormsService::SESSION_OLDINPUT_KEY);
        if ($this->request()->isAjax) {
            return $this->asJson([
                'success' => $this->session()->getFlash(FormsService::SESSION_SUCCESS_KEY)
            ]);
        }
        if ($form->redirect) {
            return $this->redirect($form->redirect);
        }
        return $this->redirect($this->request()->getReferrer().'?success=1');
    }

    protected function onSpam(Form $form)
    {
        if($this->request()->isAjax) {
            return $this->asJson(['success' => true]);
        }
        return $this->redirect($this->request()->getReferrer());
    }

    protected function onUnvalidForm(Form $form)
    {
        if ($this->request()->isAjax) {
            $response = [
                'errors' => $this->session()->getFlash(FormsService::SESSION_ERRORS_KEY)
            ];
            $this->response->setStatusCode(422, 'Validation errors');
            return $this->asJson($response);
        }
        return $this->redirect($this->request()->getReferrer());
    }
}