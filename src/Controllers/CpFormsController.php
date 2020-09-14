<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\HookAsset;
use Inspire\Forms\Assets\FormAsset;
use Inspire\Forms\Assets\FormsAsset;
use Inspire\Forms\Models\Condition;
use Inspire\Forms\Models\Form;
use Inspire\Forms\Models\FormSubmission;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class CpFormsController extends Controller
{
	public function actionForm(int $id = null, string $menu = 'config')
	{
        $template = 'create';
        if ($id) {
            $template = 'edit';
            $form = $this->forms()->getById($id);
            if (!$form) {
                throw new NotFoundHttpException(\Craft::t('inspireforms', "Form with id {id} doesn't exist", ['id' => $id]));
            }
        } else {
            $form = new Form;
        }

        $fields = $form->fields;
        $rules = $form->rules;
        unset($form->fields);
        unset($form->rules);

        $this->view->registerAssetBundle(HookAsset::class);
        foreach ($this->forms()->getRegisteredAssets() as $asset) {
            $this->view->registerAssetBundle($asset);
        }
        $this->view->registerAssetBundle(FormsAsset::class);

		$this->renderTemplate('inspireforms/cp/forms/'.$template, [
            'menu' => $menu,
            'form' => $form,
            'notifications' => $this->notifications()->getAll(),
            'captchas' => $this->captchas()->getAllAsArray(),
            'fields' => $fields,
            'rules' => $rules,
            'validConditions' => Condition::validTypes(),
            'registeredFields' => $this->fields()->getAllRegistered(),
            'registeredRules' => $this->rules()->getAllRegistered(),
            'volumes' => array_map(function($volume) {
                return ['id' => $volume->id, 'name' => $volume->name];
            }, \Craft::$app->volumes->getAllVolumes())
        ]);
	}

	public function actionIndex()
	{
		$this->renderTemplate('inspireforms/cp/forms/index', [
            'forms' => $this->forms()->getAll()
        ]);
	}

	public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $formData = $this->request()->getRequiredParam('form');
        $fieldsData = $this->request()->getRequiredParam('fields');
        $rulesData = $this->request()->getRequiredParam('rules');
        
        $form = $this->forms()->validateData($formData);
        $form->fields = $this->fields()->validateData($fieldsData);
        $form->rules = $this->rules()->validateData($rulesData);

        if ($errors = $this->forms()->gatherErrors($form)) {
            return $this->asJson([
                'errors' => $errors,
                'message' => \Craft::t('inspireforms', 'Please fix the errors before saving.')
            ]);
        }
        if ($this->forms()->save($form)) {
            return $this->asJson([
                'success' => true,
                'message' => \Craft::t('inspireforms', 'Form saved successfully.'),
                'form' => $form
            ]);    
        }
        return $this->asJson([
            'message' => \Craft::t('inspireforms', 'Unexpected error happened.')
        ]);
    }

    public function actionDelete(int $id)
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $form = $this->forms()->getById($id);
        if (!$form) {
            throw new BadRequestHttpException(\Craft::t('inspireforms', "Form with id {id} doesn't exist", ['id' => $id]));
        }
        if (!$this->forms()->delete($form)) {
            throw new BadRequestHttpException(\Craft::t('inspireforms', "Form {id} couldn't be deleted", ['id' => $id]));
        }
        return $this->asJson([
            'success' => true,
        ]);
    }
}