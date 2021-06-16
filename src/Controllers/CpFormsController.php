<?php 

namespace Ryssbowh\Formidable\Controllers;

use Inspire\Emails\Emails;
use Ryssbowh\Formidable\Assets\FormAsset;
use Ryssbowh\Formidable\Assets\FormsAsset;
use Ryssbowh\Formidable\Assets\HookAsset;
use Ryssbowh\Formidable\Models\Condition;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class CpFormsController extends Controller
{
	public function actionForm(int $id = null, string $tab = 'config')
	{
        $template = 'create';
        if ($id) {
            $template = 'edit';
            $form = $this->forms()->getById($id);
            if (!$form) {
                throw new NotFoundHttpException(\Craft::t('formidable', "Form with id {id} doesn't exist", ['id' => $id]));
            }
        } else {
            $form = new Form;
        }

        $fields = $form->getFields();
        $rules = $form->getRules();

        $this->view->registerAssetBundle(HookAsset::class);
        foreach ($this->forms()->getRegisteredAssets() as $asset) {
            $this->view->registerAssetBundle($asset);
        }
        $this->view->registerAssetBundle(FormsAsset::class);

		$this->renderTemplate('formidable/cp/forms/'.$template, [
            'loaded' => time(),
            'tab' => $tab,
            'form' => $form,
            'forms' => $this->forms()->getAllAsArray(),
            'emails' => array_map(function ($email) {
                return $email->toArray();
            }, Emails::$plugin->emails->all()),
            'captchas' => $this->captchas()->getAllAsArray(),
            'fields' => $fields,
            'rules' => $rules,
            'registeredFields' => $this->fields()->getAllRegistered(),
            'registeredRules' => $this->rules()->getAllRegistered(),
            'volumes' => array_map(function($volume) {
                return ['id' => $volume->id, 'name' => $volume->name];
            }, \Craft::$app->volumes->getAllVolumes())
        ]);
	}

	public function actionGet(int $id)
	{
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
            'fields' => $form->getFields(),
            'rules' => $form->getRules(),
        ]);    
	}

    public function actionNew()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        return $this->asJson([
            'loaded' => time(),
            'success' => true,
            'form' => new Form,
        ]);    
    }

	public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $loaded = $this->request()->getRequiredParam('loaded');
        $formData = $this->request()->getRequiredParam('form');
        $fieldsData = $this->request()->getRequiredParam('fields');
        $rulesData = $this->request()->getRequiredParam('rules');
        
        $form = $this->forms()->validateData($formData);

        if ($form->dateUpdated && $form->dateUpdated->getTimestamp() > $loaded) {
            return $this->asJson([
                'message' => \Craft::t('formidable', 'This form has been updated since you last loaded it, please reload the page')
            ]);
        }

        $form->fields = $this->fields()->validateData($fieldsData);
        $form->rules = $this->rules()->validateData($rulesData);

        if ($errors = $this->forms()->gatherErrors($form)) {
            return $this->asJson([
                'errors' => $errors,
                'message' => \Craft::t('formidable', 'Please fix the errors before saving.')
            ]);
        }
        if ($this->forms()->save($form)) {
            return $this->asJson([
                'loaded' => time(),
                'success' => true,
                'message' => \Craft::t('formidable', 'Form saved successfully.'),
                'form' => $form
            ]);    
        }
        return $this->asJson([
            'message' => \Craft::t('formidable', 'Unexpected error happened.')
        ]);
    }

    public function actionDelete(int $id)
    {
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