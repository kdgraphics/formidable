<?php

namespace Inspire\Formidable\controllers;

use Inspire\Formidable\exceptions\FormEmailException;
use Inspire\Formidable\models\Form;
use Inspire\Formidable\services\FormsService;
use yii\web\HttpException;

class FormsController extends Controller
{
    protected array|int|bool$allowAnonymous = self::ALLOW_ANONYMOUS_LIVE;

    public function actionSubmit(int $id)
    {
        $form = $this->forms()->getById($id);
        if (!$form) {
            throw new HttpException(404);
        }
        if ($form->method == 'POST') {
            $this->requirePostRequest();
        }

        try {
            $result = $this->forms()->submit($form);
        } catch (FormEmailException $e) {
            $this->response->setStatusCode(500);
            return $this->asJson([
                'errors' => [
                    '_emailing' => [\Craft::t('formidable', "Your submission is saved but there's been a problem while sending out emails. We have been informed of the problem")]
                ]
            ]);
        }

        if ($result === FormsService::INVALID or $result === FormsService::SPAM) {
            return $this->onUnvalidForm($form);
        }
        return $this->onValidSubmission($form);
    }

    protected function onValidSubmission(Form $form)
    {
        $this->forms()->getSessionOldInput($form->handle);
        if ($this->request()->isAjax) {
            return $this->asJson([
                'success' => $this->forms()->getSessionSuccess($form->handle)
            ]);
        }
        return $this->redirectAfterValidSubmission($form);
    }

    protected function onUnvalidForm(Form $form)
    {
        if ($this->request()->isAjax) {
            $response = [
                'errors' => $this->forms()->getSessionErrors($form->handle)
            ];
            $this->response->setStatusCode(422, 'Validation errors');
            return $this->asJson($response);
        }
        return $this->redirect($this->request()->getReferrer());
    }

    protected function redirectAfterValidSubmission(Form $form)
    {
        if ($form->redirect) {
            return $this->redirect($form->redirect);
        }
        $referer = parse_url($this->request()->getReferrer());
        $params = explode('&', $referer['query'] ?? '');
        if (!in_array('success=1', $params)) {
            $params[] = 'success=1';
        }
        return $this->redirect(( $referer['scheme'] ?? 'http') . '://' . $referer['host'] . ($referer['path'] ?? '') . '?' . implode('&', $params));
    }
}
