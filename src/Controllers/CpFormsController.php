<?php
namespace Inspire\Formidable\Controllers;

use Inspire\Emails\Emails;
use Inspire\Formidable\Assets\FormAsset;
use Inspire\Formidable\Assets\FormsAsset;
use Inspire\Formidable\Models\Condition;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormSubmission;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class CpFormsController extends Controller
{
    public function actionForm(int $id = null, string $tab = 'config')
    {
        $this->requirePermission('manageFormidableForms');
        $template = 'create';
        if ($id) {
            $template = 'edit';
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
            'registeredRules' => $this->rules()->getAllRegistered()
        ]);
    }

    public function actionGet()
    {
        $this->requirePermission('manageFormidableForms');
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $id = $this->request->getRequiredParam('id');
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

    public function actionNew()
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

    public function actionSave()
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
                'false' => $success,
                'message' => \Craft::t('formidable', 'This form has been updated since you last loaded it, please reload the page')
            ]);
        }

        $form->fields = $this->fields()->populateFromData($fieldsData, $form);
        $form->rules = $this->rules()->populateFromData($rulesData, $form);
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

    public function actionDelete()
    {
        $this->requirePermission('manageFormidableForms');
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $id = $this->request->getRequiredParam('id');
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