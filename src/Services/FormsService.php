<?php 

namespace Inspire\Forms\Services;

use Inspire\Forms\Events\FormEvent;
use Inspire\Forms\Events\SpamEvent;
use Inspire\Forms\Events\SubmissionEvent;
use Inspire\Forms\Forms;
use Inspire\Forms\Models\Form as FormModel;
use Inspire\Forms\Models\FormSubmission;
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
    const EVENT_BEFORE_SUBMITTING = 'before_submitting';
    const EVENT_AFTER_SUBMITTING = 'after_submitting';
    const EVENT_ON_SPAM = 'on_spam';

    const SESSION_ERRORS_KEY = 'inspireforms_errors';
    const SESSION_SUCCESS_KEY = 'inspireforms_success';
    const SESSION_OLDINPUT_KEY = 'inspireforms_oldinput';

	const CONFIG_KEY = 'inspireforms.forms';

    const SPAM = 1;
    const VALID = 2;
    const INVALID = 3;

    /**
     * Forms keyed by id
     * @var array
     */
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
				$this->forms[$record->id] = $record->toModel();
			}
		}
		return $this->forms;
	}

    public function getAllAsArray(): array
    {
        $forms = ['' => \Craft::t('inspireforms', 'All forms')];
        foreach ($this->getAll() as $form) {
            $forms[$form->id] = $form->name;
        }
        return $forms;
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
		return $this->getAll()[$id] ?? null;
	}

    /**
     * Get form model by handle
     * 
     * @param  string $handle
     * @return ?FormModel
     */
    public function getByHandle(string $handle): ?FormModel
    {
        return ArrayHelper::firstWhere($this->getAll(), 'handle', $handle);
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
            'saveSubmissions' => $model->saveSubmissions,
            'redirect' => $model->redirect,
            'class' => $model->class,
            'submitLabel' => $model->submitLabel,
            'method' => $model->method,
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
            $record->redirect = $data['redirect'];
            $record->class = $data['class'];
            $record->method = $data['method'];
            $record->submitLabel = $data['submitLabel'];
            $record->uid = $formUid;

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

    public function submit(FormModel $form): int
    {
        $session = \Craft::$app->session;
        $posted = \Craft::$app->request->getBodyParams();

        if ($form->honeypot) {
            if (isset($posted[$form->honeypotField]) and !empty($posted[$form->honeypotField])) {
                $spam = Forms::$plugin->spam->create($form);
                $this->trigger(self::EVENT_ON_SPAM, new SpamEvent([
                    'spam' => $spam,
                    'form' => $form
                ]));
                return self::SPAM;
            }
        }

        $values = [];
        foreach ($form->getFields() as $field) {
            $values[$field->name] = $posted[$field->name] ?? null;
        }
        $session->setFlash(self::SESSION_OLDINPUT_KEY, $values);
        $formSubmission = new FormSubmission($values);
        foreach ($form->getFields() as $field) {
            foreach ($field->getSiteRules() as $rule) {
                $formSubmission->addRule(...$rule);
            }
        }

        $this->trigger(self::EVENT_BEFORE_SUBMITTING, new SubmissionEvent([
            'submission' => $formSubmission,
            'form' => $form
        ]));

        if ($formSubmission->validate()) {
            $submission = Forms::$plugin->submissions->create($form, $formSubmission);
            $session->setFlash(self::SESSION_SUCCESS_KEY, true);
            $this->trigger(self::EVENT_AFTER_SUBMITTING, new SubmissionEvent([
                'submission' => $submission,
                'form' => $form
            ]));
            return self::VALID;
        }
        $session->setFlash(self::SESSION_ERRORS_KEY, $formSubmission->getErrors());
        return self::INVALID;
    }
}