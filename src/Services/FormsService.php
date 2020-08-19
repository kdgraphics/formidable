<?php 

namespace Inspire\Forms\Services;

use Inspire\Forms\Events\FormEvent;
use Inspire\Forms\Models\Form as FormModel;
use Inspire\Forms\Models\Records\Form as FormRecord;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;

class FormsService extends Component
{
	const EVENT_BEFORE_SAVE = 'before_save';
	const EVENT_AFTER_SAVE = 'after_save';
	const EVENT_BEFORE_APPLY_DELETE = 'before_apply_delete';
	const EVENT_AFTER_DELETE = 'after_delete';
    const EVENT_BEFORE_DELETE = 'before_delete';
	const CONFIG_KEY = 'inspireforms.forms';

	protected $forms;

	/**
	 * Get all forms
	 * 
	 * @return array
	 */
	public function getAll(): array
	{
		if (is_null($this->forms)) {
			$this->forms = [];
			foreach (FormRecord::find()->all() as $record) {
				$this->forms[] = $record->toModel();
			}
		}
		return $this->forms;
	}

	/**
     * Get form record by uid
     * 
     * @param  string $uid
     * @return FormRecord
     */
	public function getRecordByUid(string $uid): FormRecord
	{
		return FormRecord::findOne(['uid' => $uid]) ?? new FormRecord();
	}

    /**
     * Get form model by id
     * 
     * @param  int $id
     * @return ?FormModel
     */
	public function getById(int $id): ?FormModel
	{
		return ArrayHelper::firstWhere($this->getAll(), 'id', $id, true);
	}

    /**
     * Get notification model by uid
     * 
     * @param  string $uid
     * @return ?FormModel
     */
	public function getByUid(string $uid): ?FormModel
    {
        return ArrayHelper::firstWhere($this->getAll(), 'uid', $uid, true);
    }

    /**
     * Save a form. Adds/Modifies the project config
     * 
     * @param  FormModel $model
     * @param  bool              $validate
     * @return bool
     */
	public function save(FormModel $model, bool $validate = true): bool
	{
		$isNew = empty($model->id);
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE, new FormEvent([
                'form' => $model,
                'isNew' => $isNew,
            ]));
        }

        if ($validate && !$model->validate()) {
            \Craft::info('Form not saved due to validation error.', __METHOD__);
            return false;
        }

        if ($isNew) {
            $model->uid = StringHelper::UUID();
        } else {
            $model->uid = Db::uidById('{{%inspireforms_forms}}', $model->id);
        }

        $projectConfig = \Craft::$app->getProjectConfig();

        $configData = [
            'name' => $model->name,
            'handle' => $model->handle,
            'notify_id' => $model->notify_id,
            'notifyAdmin_id' => $model->notifyAdmin_id,
            'notifyField' => $model->notifyField,
            'notifyEmails' => $model->notifyEmails,
            'honeypot' => $model->honeypot,
            'honeypotField' => $model->honeypotField,
            'saveSubmissions' => $model->saveSubmissions
        ];

        $configPath = self::CONFIG_KEY . '.' . $model->uid;
        $projectConfig->set($configPath, $configData);

        if ($isNew) {
            $model->id = Db::idByUid('{{%inspireforms_forms}}', $model->uid);
        }

        return true;
	}

    /**
     * Delete form model
     * 
     * @param  FormModel $model
     * @return bool
     */
    public function delete(FormModel $model): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_DELETE, new FormEvent([
                'form' => $model,
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
        $formUid = $event->tokenMatches[0];
        $data = $event->newValue;

        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $record = $this->getRecordByUid($formUid);
            $isNew = $record->getIsNewRecord();

            $record->name = $data['name'];
            $record->handle = $data['handle'];
            $record->notify_id = $data['notify_id'];
            $record->notifyAdmin_id = $data['notifyAdmin_id'];
            $record->notifyField = $data['notifyField'];
            $record->notifyEmails = $data['notifyEmails'];
            $record->honeypot = $data['honeypot'];
            $record->honeypotField = $data['honeypotField'];
            $record->saveSubmissions = $data['saveSubmissions'];

            $record->save(false);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Clear caches
        $this->forms = null;

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new FormEvent([
                'form' => $record->toModel(),
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

        $form = $this->getByUid($uid);

        if (!$form) {
            return;
        }

        if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_APPLY_DELETE, new FormEvent([
                'form' => $form,
            ]));
        }

        \Craft::$app->getDb()->createCommand()
            ->delete('{{%inspireforms_forms}}', ['uid' => $uid])
            ->execute();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE, new FormEvent([
                'form' => $form,
            ]));
        }
    }
}