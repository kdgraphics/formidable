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

	const CONFIG_KEY = 'inspireforms.1-forms';

    const SPAM = 1;
    const VALID = 2;
    const INVALID = 3;

    const CACHE_ALL = 'inspireforms.forms';

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
            // $this->forms = \Craft::$app->cache->getOrSet(self::CACHE_ALL, function () {
            //     $forms = [];
            //     foreach (FormRecord::find()->with('fields')->all() as $record) {
            //         $forms[$record->id] = $record->toModel();
            //     }
            //     return $forms;
            // });
            $this->forms = [];
            foreach (FormRecord::find()->with('fields')->all() as $record) {
                $this->forms[$record->id] = $record->toModel();
            }
        }
		return $this->forms;
	}

    /**
     * Get all forms as array 'id' => 'name'
     * 
     * @return array
     */
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

        if ($validate) {
            if (!$model->validate() or !$this->validateFields($model->fields)) {
                return false;
            }
        }

        if ($isNew) {
            $model->uid = StringHelper::UUID();
        } else {
            $model->uid = Db::uidById('{{%inspireforms_forms}}', $model->id);
        }

        $projectConfig = \Craft::$app->getProjectConfig();
        
        $configData = $model->getConfig();

        $configPath = self::CONFIG_KEY . '.' . $model->uid;
        $projectConfig->set($configPath, $configData);

        if ($isNew) {
            $model->id = Db::idByUid('{{%inspireforms_forms}}', $model->uid);
        } else {
            foreach ($model->fields as $field) {
                $field->id = Db::idByUid('{{%inspireforms_fields}}', $field->uid);
            }
            $toDelete = $this->gatherFieldsToDelete($model);
            Forms::$plugin->fields->deleteMany($toDelete);
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
        $notifications = Forms::$plugin->notifications;
        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $form = $this->getRecordByUid($formUid);
            $isNew = $form->getIsNewRecord();

            $form->name = $data['name'];
            $form->handle = $data['handle'];
            $form->notifyAdmin_id = $notifications->getRecordByUid($data['notifyAdmin'])->id ?? null;
            $form->notify_id = $notifications->getRecordByUid($data['notify'])->id ?? null;
            $form->notifyField = $data['notifyField'];
            $form->notifyEmails = $data['notifyEmails'];
            $form->honeypot = $data['honeypot'];
            $form->honeypotField = $data['honeypotField'];
            $form->saveSubmissions = $data['saveSubmissions'];
            $form->redirect = $data['redirect'];
            $form->class = $data['class'];
            $form->method = $data['method'];
            $form->submitLabel = $data['submitLabel'];
            $form->uid = $formUid;

            $form->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if (isset($data['fields'])) {
            foreach ($data['fields'] as $data) {
                $transaction = \Craft::$app->getDb()->beginTransaction();
                $field = Forms::$plugin->fields->getRecordByUid($data['uid']);
                $field->name = $data['name'];
                $field->form_id = $form->id;
                $field->order = $data['order'];
                $field->type = $data['type'];
                $field->options = $data['options'];
                $field->fieldClass = $data['fieldClass'];
                $field->containerClass = $data['containerClass'];
                $field->instructions = $data['instructions'];
                $field->uid = $data['uid'];
                $field->save(false);
                $transaction->commit();
            }
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
            ->softDelete('{{%inspireforms_forms}}', ['uid' => $uid])
            ->execute();
        \Craft::$app->getDb()->createCommand()
            ->softDelete('{{%inspireforms_fields}}', ['form_id' => $form->id])
            ->execute();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE, new FormEvent([
                'form' => $form,
            ]));
        }
    }

    /**
     * Submit a form (through front end)
     * 
     * @param  FormModel $form
     * @return int
     */
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
        foreach ($form->fields as $field) {
            $values[$field->name] = $posted[$field->name] ?? null;
        }
        $session->setFlash(self::SESSION_OLDINPUT_KEY, $values);
        $formSubmission = new FormSubmission($values);
        foreach ($form->fields as $field) {
            foreach ($field->getSiteRules() as $rule) {
                $formSubmission->addRule(...$rule);
            }
        }

        $this->trigger(self::EVENT_BEFORE_SUBMITTING, new SubmissionEvent([
            'submission' => $formSubmission,
            'form' => $form
        ]));

        foreach ($form->fields as $field) {
            $field->beforeSubmissionValidated($formSubmission);
        }

        if ($formSubmission->validate()) {
            foreach ($form->fields as $field) {
                $field->afterSubmissionValidated($formSubmission);
            }
            $submission = Forms::$plugin->submissions->create($form, $formSubmission);
            $session->setFlash(self::SESSION_SUCCESS_KEY);
            $this->trigger(self::EVENT_AFTER_SUBMITTING, new SubmissionEvent([
                'submission' => $submission,
                'form' => $form
            ]));
            return self::VALID;
        }
        $session->setFlash(self::SESSION_ERRORS_KEY, $formSubmission->getErrors());
        return self::INVALID;
    }

    /**
     * Validates an array of fields
     * 
     * @param  array  $fields
     * @return bool
     */
    protected function validateFields(array $fields): bool
    {
        $validated = true;
        foreach ($fields as $field) {
            if (!$field->validate()) {
                $validated = false;
            }
        }
        return $validated;
    }

    /**
     * Sort out fields to delete for a form
     * 
     * @param  FormModel $form
     * @return array
     */
    protected function gatherFieldsToDelete(FormModel $form): array
    {
        $allFields = Forms::$plugin->fields->getFormFields($form);
        $toDelete = [];
        foreach ($allFields as $field) {
            if (!ArrayHelper::contains($form->fields, 'id', $field->id)) {
                $toDelete[] = $field;
            }
        }
        return $toDelete;
    }
}