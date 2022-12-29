<?php

namespace Inspire\Formidable\Controllers;

use Inspire\Formidable\Assets\FormsAsset;
use Inspire\Formidable\Exceptions\FormEmailException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormPosted;
use Inspire\Formidable\Models\Submission;
use Inspire\Formidable\Services\FormsService;
use craft\helpers\ArrayHelper;
use craft\web\UploadedFile;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class FormsController extends Controller
{
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_LIVE;

    public function actionSubmit()
    {
        $id = $this->request->getRequiredParam('id');
        $form = $this->forms()->getById($id);
        if (!$form) {
            throw new HttpException(404);
        }
        if ($form->method == 'POST') {
            $this->requirePostRequest();
        }
        
        list($result, $posted, $submission) = $this->forms()->submit($form);

        if ($result === FormsService::STATUS_INVALID or $result === FormsService::STATUS_SPAM) {
            return $this->onUnvalidSubmission($posted);
        }
        return $this->onValidSubmission($submission);
    }

    public function actionUploadAssets()
    {
        $this->requireAcceptsJson();
        $volumeUid = $this->request->getRequiredParam('volumeUid');
        $folder = $this->request->getParam('folder');
        $files = UploadedFile::getInstancesByName('files');
        $data = [];
        foreach ($files as $file) {
            $asset = Formidable::$plugin->assets->uploadToVolume($file, $volumeUid, $folder);
            $data[] = [
                'id' => $asset->id,
                'url' => $asset->url,
                'kind' => $asset->kind,
                'title' => $asset->title
            ];
        }
        return $this->asJson($data);
    }

    protected function onValidSubmission(Submission $submission)
    {
        if ($this->request()->acceptsJson) {
            return $this->asJson([
                'submission' => $submission->id,
                'redirect' => $submission->form->redirect
            ]);
        }
        if ($submission->form->redirect) {
            return $this->redirect($submission->form->redirect);
        }
        return null;
    }

    protected function onUnvalidSubmission(FormPosted $posted)
    {
        $this->response->setStatusCode(400, 'Validation errors');
        if ($this->request()->acceptsJson) {
            return $this->asJson([
                'errors' => $posted->errors
            ]);
        }
        return null;
    }
}
