<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\NotificationAsset;
use Inspire\Forms\Models\Notification;

class CpNotificationsController extends Controller
{
	public function actionIndex(?int $id = null)
	{
        $this->view->registerAssetBundle(NotificationAsset::class);
        $notifications = [];
        foreach ($this->notifications()->getAll() as $notif) {
            $notifications[$notif->id] = [
                'id' => $notif->id, 
                'name' => $notif->name,
                'sentFrom' => $notif->sentFrom,
                'subject' => $notif->subject,
            ];
        }
        $notification = null;
        if ($id) {
            $notification = $this->notifications()->getById($id);
        }
		$this->renderTemplate('inspireforms/cp/notifications/index', [
            'notifications' => $notifications,
            'notification' => $notification
        ]);
	}

	public function actionEdit(int $id)
	{
		$notification = $this->notifications()->getById($id);
		if (!$notification) {
			$session->setError(\Craft::t('inspireforms', 'Can\'t find notification with id {id}.', ['id' => $id]));
			return $this->redirect("inspireforms/notifications");
		}
		return $this->create($notification);
	}

    public function actionGet(int $id)
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $notification = $this->notifications()->getById($id);
        if (!$notification) {
            return $this->asJson([
                'error' => \Craft::t('inspireforms', 'Can\'t find notification with id {id}.', ['id' => $id])
            ]);
        }
        return $this->asJson([
            'success' => true,
            'notification' => $notification
        ]);
    }

    public function actionDelete(int $id)
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $notification = $this->notifications()->getById($id);

        if (!$notification) {
            throw new BadRequestHttpException(\Craft::t('inspireforms', 'No notification model found.'));
        }
        if (!$this->notifications()->delete($notification)) {
            throw new BadRequestHttpException(\Craft::t('inspireforms', 'Notification could not be deleted.'));
        }

        return $this->asJson([
            'success' => true,
            'message' => \Craft::t('inspireforms', 'Notification has been deleted.')
        ]);
    }

	public function actionCreate()
	{
		return $this->create(new Notification);
	}

	public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $data = $this->request()->getRequiredParam('data');
        $id = $data['id'] ?? null;
        if ($id and is_numeric($id)) {
            $model = $this->notifications()->getById($id);
        } else {
            $model = new Notification();
        }

        $model->setAttributes($data);

        if (!$this->notifications()->save($model)) {
            return $this->asJson([
                'errors' => $model->getErrors()
            ]);
        }
        
        return $this->asJson([
            'message' => \Craft::t('inspireforms', 'Notification saved successfully.'),
            'success' => true,
            'notification' => $model
        ]);
    }

    protected function create(Notification $model)
    {
    	$this->view->registerAssetBundle(NotificationAsset::class);
		return $this->renderTemplate('inspireforms/cp/notifications/create', [
			'notification' => $model
		]);
    }
}