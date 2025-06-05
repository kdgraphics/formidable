<?php
namespace Inspire\Formidable\controllers;

use Inspire\Emails\Emails;
use Inspire\Formidable\Assets\FormsAsset;
use Inspire\Formidable\Assets\HookAsset;
use Inspire\Formidable\Models\Form;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CpFormsController extends Controller
{
    public function actionForm(int $id = null, string $tab = 'config'): void
    {
        $this->requirePermission('manageFormidableForms');
        $template = 'create';
        if ($id) {
            $template = 'edit';
        }

        $this->view->registerAssetBundle(HookAsset::class);
        foreach ($this->forms()->getRegisteredAssets() as $asset) {
            $this->view->registerAssetBundle($asset);
        }

        $this->view->registerAssetBundle(FormsAsset::class);

        $this->renderTemplate('formidable/cp/forms/'.$template, [
            'tab' => $tab,
            'form' => $id ?? 0,
            'forms' => $this->forms()->getAllAsArray(),
            'emails' => array_map(function ($email) {
                return $email->toArray();
            }, Emails::$plugin->emails->all()),
            'captchas' => $this->captchas()->getAllAsArray(),
            'registeredFields' => $this->fields()->getAllRegistered(),
            'registeredRules' => $this->rules()->getAllRegistered(),
            'volumes' => array_map(function($volume) {
                return ['uid' => $volume->uid, 'name' => $volume->name];
            }, \Craft::$app->volumes->getAllVolumes())
        ]);
    }

    public function actionGet(int $id): Response
    {
        $this->requirePermission('manageFormidableForms');
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $form = $this->forms()->getById($id);

        if (!$form) {
            throw new NotFoundHttpException(\Craft::t('formidable', "Form with id {id} doesn't exist", ['id' => $id]));
        }

        return $this->asJson([
            'loaded' => time(),
            'success' => true,
            'form' => $form,
            'fields' => $form->rootFields,
            'rules' => $form->rules,
        ]);
    }

    public function actionNew(): Response
    {
        $this->requirePermission('manageFormidableForms');
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        return $this->asJson([
            'loaded' => time(),
            'success' => true,
            'form' => new Form,
            'fields' => [],
            'rules' => []
        ]);
    }

    public function actionSave(): Response
    {
        $this->requirePermission('manageFormidableForms');
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $loaded = $this->request()->getRequiredParam('loaded');
        $formData = $this->request()->getRequiredParam('form');
        $fieldsData = $this->request()->getRequiredParam('fields');
        $rulesData = $this->request()->getRequiredParam('rules');

        $form = $this->forms()->populateFromData($formData);

        if ($form->dateUpdated && $form->dateUpdated->getTimestamp() > $loaded) {
            return $this->asJson([
                'success' => false,
                'message' => \Craft::t('formidable', 'This form has been updated since you last loaded it, please reload the page')
            ]);
        }

        $form->fields = $this->fields()->populateFromData($fieldsData);
        $form->rules = $this->rules()->populateFromData($rulesData);
        $form->validate();

        $success = false;
        if ($form->hasErrors()) {
            $message = \Craft::t('formidable', 'Please fix the errors before saving.');
        } else {
            if ($this->forms()->save($form)) {
                $message = \Craft::t('formidable', 'Form saved successfully.');
                $success = true;
            } else {
                $message = \Craft::t('formidable', 'Unexpected error happened.');
            }
        }
        return $this->asJson([
            'loaded' => time(),
            'success' => $success,
            'message' => $message,
            'form' => $form,
            'fields' => $form->rootFields,
            'rules' => $form->rules
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $this->requirePermission('manageFormidableForms');
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $form = $this->forms()->getById($id);
        if (!$form) {
            throw new BadRequestHttpException(\Craft::t('formidable', "Form with id {id} doesn't exist", ['id' => $id]));
        }
        if (!$this->forms()->delete($form)) {
            return $this->asJson([
                'message' => \Craft::t('formidable', "Form couldn't be deleted")
            ]);
        }
        return $this->asJson([
            'success' => true,
            'message' => \Craft::t('formidable', "Form deleted successfully")
        ]);
    }
}
