<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\NotificationAsset;
use Inspire\Forms\Models\Notification;
use yii\web\BadRequestHttpException;

class CpNotificationsController extends Controller
{
	public function actionIndex(?int $id = null)
	{
        $this->view->registerAssetBundle(NotificationAsset::class);
		$this->renderTemplate('inspireforms/cp/notifications/index', [
            'notifications' => $this->notifications()->getAll(),
            'notificationId' => $id,
            'loaded' => time()
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

        $notifications = $this->notifications()->getAll();

        return $this->asJson([
            'success' => true,
            'message' => \Craft::t('inspireforms', 'Notification has been deleted.'),
            'id' => $id,
            'loaded' => time(),
            'notifications' => $notifications,
            'id' => $notifications ? $notifications[array_keys($notifications)[0]]->id : null
        ]);
    }

	public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $loaded = $this->request()->getRequiredParam('loaded');
        $data = $this->request()->getRequiredParam('data');

        $id = $data['id'] ?? null;
        $model = new Notification();
        if ($id and is_numeric($id)) {
            $model = $this->notifications()->getById($id);
        }

        if ($model->dateUpdated && $model->dateUpdated->getTimestamp() > $loaded) {
            return $this->asJson([
                'message' => \Craft::t('inspireforms', 'This notification has been updated since you last loaded it, please reload the page')
            ]);
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
            'id' => $model->id,
            'loaded' => time(),
            'notifications' => $this->notifications()->getAll()
        ]);
    }
}