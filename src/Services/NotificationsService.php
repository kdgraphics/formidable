<?php 

namespace Ryssbowh\Formidable\Services;

use Psr\Log\LogLevel;
use Ryssbowh\Formidable\Events\EmailEvent;
use Ryssbowh\Formidable\Events\NotificationEvent;
use Ryssbowh\Formidable\Exceptions\NotificationException;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Notification as NotificationModel;
use Ryssbowh\Formidable\Models\Records\Form as FormRecord;
use Ryssbowh\Formidable\Models\Records\Notification as NotificationRecord;
use Ryssbowh\Formidable\Models\Records\Submission;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\events\RebuildConfigEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\web\View;
use yii\base\Event;

class NotificationsService extends Component
{
	const EVENT_BEFORE_SAVE = 'before_save';
	const EVENT_AFTER_SAVE = 'after_save';
	const EVENT_BEFORE_APPLY_DELETE = 'before_apply_delete';
	const EVENT_AFTER_DELETE = 'after_delete';
    const EVENT_BEFORE_DELETE = 'before_delete';

    const EVENT_BEFORE_SENDING_EMAIL = 'before_email';
    const EVENT_AFTER_SENDING_EMAIL = 'after_email';

	const CONFIG_KEY = Formidable::CONFIG_KEY . '.notifications';

	protected $notifications;

    /**
     * Get all notification models
     * 
     * @return array
     */
	public function getAll(): array
	{
		if (is_null($this->notifications)) {
			$this->notifications = [];
			foreach (NotificationRecord::find()->all() as $record) {
				$this->notifications[$record->id] = $record->toModel();
			}
		}
		return $this->notifications;
	}

    /**
     * All notifications names, keyed by id
     * @return array
     */
    public function getAllAsArray(): array
    {
        $notifications = [];
        foreach ($this->getAll() as $notification) {
            $notifications[$notification->id] = $notification->name;
        }
        return $notifications;
    }

    /**
     * Get notification record by uid
     * 
     * @param  string $uid
     * @return NotificationRecord
     */
	public function getRecordByUid(string $uid): NotificationRecord
	{
		return NotificationRecord::findOne(['uid' => $uid]) ?? new NotificationRecord();
	}

    /**
     * Get notification by id
     * 
     * @param  int $id
     * @param  bool $throwIfNotFound
     * @return ?NotificationModel
     */
    public function getById(int $id, bool $throwIfNotFound = false): ?NotificationModel
    {
        $form = $this->getAll()[$id] ?? null;
        if (!$form and $throwIfNotFound) {
            throw NotificationException::idNotFound($id);
        }
        return $form;
    }

    /**
     * Get notification model by uid
     * 
     * @param  string $uid
     * @return ?NotificationModel
     */
	public function getByUid(string $uid): ?NotificationModel
    {
        return ArrayHelper::firstWhere($this->getAll(), 'uid', $uid, true);
    }

    /**
     * Save a notification. Adds/Modifies the project config
     * 
     * @param  NotificationModel $model
     * @param  bool              $validate
     * @return bool
     */
	public function save(NotificationModel $model, bool $validate = true): bool
	{
		$isNew = empty($model->id);
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE, new NotificationEvent([
                'notification' => $model,
                'isNew' => $isNew,
            ]));
        }

        if ($validate && !$model->validate()) {
            \Craft::info('Notification not saved due to validation error.', __METHOD__);
            return false;
        }

        if ($isNew) {
            $model->uid = StringHelper::UUID();
        } else {
            $model->uid = Db::uidById(NotificationRecord::tableName(), $model->id);
        }

        $projectConfig = \Craft::$app->getProjectConfig();

        $configData = $model->getConfig();

        $configPath = self::CONFIG_KEY . '.' . $model->uid;
        $projectConfig->set($configPath, $configData);

        if ($isNew) {
            $model->id = Db::idByUid(NotificationRecord::tableName(), $model->uid);
        }

        return true;
	}

    /**
     * Delete notification model
     * 
     * @param  NotificationModel $model
     * @return bool
     */
    public function delete(NotificationModel $model): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_DELETE, new NotificationEvent([
                'notification' => $model,
            ]));
        }

        \Craft::$app->getProjectConfig()->remove(self::CONFIG_KEY . '.' . $model->uid);

        return true;
    }

	/**
	 * Handle a added or modified config
	 * 
	 * @param ConfigEvent $event
	 */
	public function handleChanged(ConfigEvent $event)
    {
        $notificationUid = $event->tokenMatches[0];
        $data = $event->newValue;

        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $record = $this->getRecordByUid($notificationUid);
            $isNew = $record->getIsNewRecord();

            $record->name = $data['name'];
            $record->method = $data['method'];
            $record->template = $data['template'];
            $record->html = $data['html'];
            $record->sentFrom = $data['sentFrom'];
            $record->sentFromName = $data['sentFromName'];
            $record->subject = $data['subject'];
            $record->uid = $notificationUid;

            $record->save(false);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Clear caches
        $this->notifications = null;

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new NotificationEvent([
                'notification' => $record->toModel(),
                'isNew' => $isNew,
            ]));
        }
    }

    /**
     * Handle a deleted config
     * 
     * @param ConfigEvent $event
     */
    public function handleDeleted(ConfigEvent $event)
    {
        $uid = $event->tokenMatches[0];

        $notification = $this->getByUid($uid);

        if (!$notification) {
            return;
        }

        if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_APPLY_DELETE, new NotificationEvent([
                'notification' => $notification,
            ]));
        }

        \Craft::$app->getDb()->createCommand()
            ->delete(NotificationRecord::tableName(), ['uid' => $uid])
            ->execute();

        // Clear caches
        $this->notifications = null;

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE, new NotificationEvent([
                'notification' => $notification,
            ]));
        }
    }

    /**
     * Respond to rebuild config event
     * 
     * @param  RebuildConfigEvent $e
     */
    public function rebuildConfig(RebuildConfigEvent $e)
    {
        foreach ($this->getAll() as $notification) {
            $e->config[Formidable::CONFIG_KEY][self::CONFIG_KEY][$notification->uid] = $notification->getConfig();
        }
    }

    /**
     * Sends an email
     * 
     * @param  Submission|Spam|null $submitted
     * @param  string|array         $sentFrom
     * @param  array                $emails
     * @param  string               $subject
     * @param  string               $html
     * @param  string               $type
     * @return bool
     */
    public function sendEmail($submitted, $sentFrom, array $emails, string $subject, string $html, string $type): bool
    {
        $email = \Craft::$app
            ->getMailer()
            ->compose()
            ->setFrom($sentFrom)
            ->setTo($emails)
            ->setSubject($subject)
            ->setHtmlBody($html);
        $event = new EmailEvent([
            'email' => $email,
            'submitted' => $submitted,
            'type' => $type,
        ]);
        $this->trigger(self::EVENT_BEFORE_SENDING_EMAIL, $event);
        if ($email->send()) {
            $this->trigger(self::EVENT_AFTER_SENDING_EMAIL, $event);
            return true;
        }
        return false;
    }

    /**
     * Send the notification to some emails when a form is submitted or schedule it
     * 
     * @param  NotificationModel    $notification
     * @param  FormSubmission       $submission
     * @param  Submission|null|Spam $submitted
     * @param  array                $emails
     * @param  string               $type
     * @return bool
     */
    public function send(NotificationModel $notification, FormSubmission $submission, $submitted, array $emails, string $type): bool
    {
        $html = $this->getHtml($notification, $submission, $submitted);
        $subject = $this->getSubject($notification, $submission, $submitted);
        $from = $notification->normalizeSentFrom();
        if (Formidable::$plugin->settings->sendInSchedule) {
            return Formidable::$plugin->schedule->create($submitted, $from, $emails, $subject, $html, $type);
        } else {
            return $this->sendEmail($submitted, $from, $emails, $subject, $html, $type);
        }
    }

    /**
     * Get html for a notification
     * 
     * @param  NotificationModel $notification
     * @param  FormSubmission    $submission
     * @return string
     */
    protected function getHtml(NotificationModel $notification, FormSubmission $submission, $submitted): string
    {
        $oldMode = $this->view->getTemplateMode();
        $this->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
        $variables = [
            'submission' => $submission,
            'submitted' => $submitted,
            'notification' => $notification,
            'form' => $submission->getForm()
        ];
        if ($notification->method == 'template') {
            $html = $this->view->renderTemplate($notification->template, $variables);
        } else {
            $html = $this->view->renderString($notification->html, $variables);
        }
        $this->view->setTemplateMode($oldMode);
        return $html;
    }

    /**
     * Replacesvariables in subject
     * 
     * @param  NotificationModel $notification
     * @param  FormSubmission    $submission
     * @return string
     */
    protected function getSubject(NotificationModel $notification, FormSubmission $submission)
    {
        $variables = [
            'submission' => $submission,
            'notification' => $notification,
            'form' => $submission->getForm()
        ];
        return $this->view->renderString($notification->subject, $variables);
    }

    protected function getView(): View
    {
        return \Craft::$app->getView();
    }
}