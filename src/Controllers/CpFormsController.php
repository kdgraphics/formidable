<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\FormsAsset;
use Inspire\Forms\Models\Form;
use Inspire\Forms\Models\FormSubmission;
use craft\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class CpFormsController extends Controller
{
	public function actionIndex()
	{
		$this->renderTemplate('inspireforms/cp/forms/index', [
            'forms' => $this->forms()->getAll()
        ]);
	}

	public function actionCreate()
	{
		return $this->create(new Form, []);
	}

	public function actionEdit(int $id)
	{
		$form = $this->forms()->getById($id);
		if (!$form) {
			$session->setError(\Craft::t('inspireforms', 'Can\'t find form with id {id}.', ['id' => $id]));
			return $this->redirect("inspireforms/form");
		}
		return $this->create($form, $form->getFields());
	}

	public function actionSave()
    {
        $this->requirePostRequest();
        $data = $this->request()->getRequiredParam('data');
        $fieldsData = $this->request()->getParam('fields', []);
        $fieldsValidated = true;
        $editing = $data['id'] ?? null;
        if ($editing and is_numeric($data['id'])) {
            $model = $this->forms()->getById($data['id']);
            list($fieldsValidated, $fields) = $this->validateFields($fieldsData);
        } else {
            $model = new Form();
        }

        $model->setAttributes($data);
        $formValidated = $model->validate();

        if ($fieldsValidated and $formValidated) {
            $this->forms()->save($model, false);
            if ($editing) {
                $this->fields()->saveMany($fields, $model, false);
                $toDelete = $this->gatherFieldsToDelete($model, $fields);
                $this->fields()->deleteMany($toDelete);
            }
            $this->session()->setNotice(\Craft::t('inspireforms', 'Form saved successfully.'));
            return $this->redirect("inspireforms/forms");
        } else {
            $this->session()->setError(\Craft::t('inspireforms', 'Please fix the errors before saving.'));
            return $this->create($model, $fields);
        }
    }

    protected function gatherFieldsToDelete(Form $form, array $fields)
    {
        $allFields = $form->getFields();
        $toDelete = [];
        foreach ($allFields as $field) {
            if (!ArrayHelper::contains($fields, 'id', $field->id)) {
                $toDelete[] = $field;
            }
        }
        return $toDelete;
    }

    protected function validateFields(array $data)
    {
        $fields = [];
        $validated = true;
        $order = 0;
        foreach ($data as $fdata) {
            if (!isset($fdata['handle'])) {
                $validated = false;
                continue;
            }
            if ($fdata['id']) {
                $field = $this->fields()->getById((int)$fdata['id']);
                if (!$field) {
                    $validated = false;
                    continue;
                }
            } else {
                $field = $this->fields()->getRegistered($fdata['handle']);
            }
            $field->order = $order;
            $field->setAttributes($fdata);
            $validated = ($validated and $field->validate());
            $fields[] = $field;
            $order++;
        }
        return [$validated, $fields];
    }

    protected function create(Form $model, array $fields)
    {
    	$this->view->registerAssetBundle(FormsAsset::class);
		return $this->renderTemplate('inspireforms/cp/forms/create', [
			'form' => $model,
			'notifications' => $this->notifications()->getAllAsArray(),
			'fields' => $fields,
			'registeredFields' => $this->fields()->getAllRegistered()
		]);
    }
}