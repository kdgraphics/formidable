<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\FormAsset;
use Inspire\Forms\Assets\FormsAsset;
use Inspire\Forms\Models\Form;
use Inspire\Forms\Models\FormSubmission;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class CpFormsController extends Controller
{
	public function actionIndex()
	{
        $this->view->registerAssetBundle(FormsAsset::class);
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
		return $this->create($form);
	}

	public function actionSave()
    {
        $this->requirePostRequest();
        $data = $this->request()->getRequiredParam('data');
        $fieldsData = $this->request()->getParam('fields', []);
        $editing = $data['id'] ?? null;
        if ($editing and is_numeric($data['id'])) {
            $model = $this->forms()->getById($data['id']);
            $model->fields = $this->buildFields($fieldsData);
        } else {
            $model = new Form();
        }

        $model->setAttributes($data);

        if ($this->forms()->save($model)) {
            $this->session()->setNotice(\Craft::t('inspireforms', 'Form saved successfully.'));
            return $this->redirect("inspireforms/forms");
        } else {
            $this->session()->setError(\Craft::t('inspireforms', 'Please fix the errors before saving.'));
            return $this->create($model);
        }
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

    protected function buildFields(array $data): array
    {
        $fields = [];
        $order = 0;
        foreach ($data as $fdata) {
            if (!isset($fdata['handle'])) {
                continue;
            }
            if ($fdata['id']) {
                $field = $this->fields()->getById((int)$fdata['id']);
                if (!$field) {
                    continue;
                }
            } else {
                $field = $this->fields()->getRegistered($fdata['handle']);
                $field->uid = StringHelper::UUID();
            }
            $field->order = $order;
            $field->setAttributes($fdata);
            $fields[] = $field;
            $order++;
        }
        return $fields;
    }

    protected function create(Form $model)
    {
    	$this->view->registerAssetBundle(FormAsset::class);
		return $this->renderTemplate('inspireforms/cp/forms/create', [
			'form' => $model,
			'notifications' => $this->notifications()->getAllAsArray(),
			'fields' => $model->fields,
			'registeredFields' => $this->fields()->getAllRegistered()
		]);
    }
}