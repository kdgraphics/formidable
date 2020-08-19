<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\FormsAsset;
use Inspire\Forms\Forms;
use Inspire\Forms\Models\Form;
use craft\web\Controller;

class FormsController extends Controller
{
	public function actionIndex()
	{
		$this->renderTemplate('inspireforms/cp/forms/index', [
            'forms' => Forms::$plugin->forms->getAll()
        ]);
	}

	public function actionCreate()
	{
		return $this->create(new Form);
	}

	public function actionEdit(int $id)
	{
		$form = Forms::$plugin->forms->getById($id);
		if (!$form) {
			$session->setError(\Craft::t('inspireforms', 'Can\'t find form with id {id}.', ['id' => $id]));
			return $this->redirect("inspireforms/form");
		}
		return $this->create($form);
	}

	public function actionSave()
    {
        $this->requirePostRequest();
        $request = \Craft::$app->request;
        $session = \Craft::$app->session;
        $data = $request->getRequiredParam('data');
        $id = $data['id'] ?? null;
        if ($id and is_numeric($id)) {
            $model = Forms::$plugin->forms->getById($id);
        } else {
            $model = new Form();
        }

        $model->setAttributes($data);

        if (!Forms::$plugin->forms->save($model)) {
        	return $this->create($model);
        } else {
            $session->setNotice(\Craft::t('inspireforms', 'Form saved successfully.'));
            return $this->redirect("inspireforms/forms");
        }
    }

    protected function create(Form $model)
    {
    	$this->view->registerAssetBundle(FormsAsset::class);
		return $this->renderTemplate('inspireforms/cp/forms/create', [
			'form' => $model,
			'notifications' => Forms::$plugin->notifications->getAllAsArray(),
			'fields' => $model->getFields(),
			'registeredFields' => Forms::$plugin->fields->getAllRegistered()
		]);
    }
}