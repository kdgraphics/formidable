<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\NotificationAsset;
use Inspire\Forms\Forms;
use Inspire\Forms\Models\Notification;
use craft\web\Controller;

class NotificationsController extends Controller
{
	public function actionIndex()
	{
        $this->view->registerAssetBundle(NotificationAsset::class);
		$this->renderTemplate('inspireforms/cp/notifications/index', [
            'notifications' => Forms::$plugin->notifications->getAll(),
        ]);
	}

	public function actionEdit(int $id)
	{
		$notification = Forms::$plugin->notifications->getById($id);
		if (!$notification) {
			$session->setError(\Craft::t('inspireforms', 'Can\'t find notification with id {id}.', ['id' => $id]));
			return $this->redirect("inspireforms/notifications");
		}
		return $this->create($notification);
	}

    public function actionDelete(int $id)
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = \Craft::$app->getRequest();

        $notification = Forms::$plugin->notifications->getById($id);

        if (!$notification) {
            return $this->asJson(['error' => \Craft::t('inspireforms', 'No notification model found.')]);
        }

        if (!Forms::$plugin->notifications->delete($notification)) {
            return $this->asJson(['error' => $notification->getErrors()]);
        }

        return $this->asJson(['success' => true]);
    }

	public function actionCreate()
	{
		return $this->create(new Notification);
	}

	public function actionSave()
    {
        $this->requirePostRequest();
        $request = \Craft::$app->request;
        $session = \Craft::$app->session;
        $data = $request->getRequiredParam('data');
        $id = $data['id'] ?? null;
        if ($id and is_numeric($id)) {
            $model = Forms::$plugin->notifications->getById($id);
        } else {
            $model = new Notification();
        }

        $model->setAttributes($data);

        if (!Forms::$plugin->notifications->save($model)) {
        	return $this->create($model);
        } else {
            $session->setNotice(\Craft::t('inspireforms', 'Notification saved successfully.'));
            return $this->redirect("inspireforms/notifications");
        }
    }

    protected function create(Notification $model)
    {
    	$this->view->registerAssetBundle(NotificationAsset::class);
		return $this->renderTemplate('inspireforms/cp/notifications/create', [
			'notification' => $model
		]);
    }
}