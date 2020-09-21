<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Events\FormEvent;
use Ryssbowh\Formidable\Events\HookAssetsEvent;
use Ryssbowh\Formidable\Events\SpamEvent;
use Ryssbowh\Formidable\Events\SubmissionEvent;
use Ryssbowh\Formidable\Exceptions\FormException;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Form as FormModel;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Records\Form as FormRecord;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\events\RebuildConfigEvent;
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

    const SESSION_ERRORS_KEY = 'formidable_errors';
    const SESSION_SUCCESS_KEY = 'formidable_success';
    const SESSION_OLDINPUT_KEY = 'formidable_oldinput';

    const EVENT_HOOK_ASSETS = 'formidable_hook';

	const CONFIG_KEY = Formidable::CONFIG_KEY . '.forms';

    const SPAM = 1;
    const VALID = 2;
    const INVALID = 3;

    const CACHE_ALL = 'formidable.forms';

    /**
     * Forms keyed by id
     * @var array
     */
	protected $forms;

    /**
     * Registered fields assets. Assets typically provides with Vue components to render the fields.
     * Components must be registered in window.formFields
     * 
     * @var array
     */
    protected $fieldsAssets;

    /**
     * Get all the registered assets. triggers the event for other plugins to register assets
     * 
     * @return array
     */
    public function getRegisteredAssets(): array
    {
        if ($this->fieldsAssets == null) {
            $event = new HookAssetsEvent;
            $this->trigger(self::EVENT_HOOK_ASSETS, $event); 
            $this->fieldsAssets = $event->getAssets();
        }
        return $this->fieldsAssets;
    }

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
            foreach (FormRecord::find()->all() as $record) {
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
        $forms = [];
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
		return FormRecord::findOne(['uid' => $uid]) ?? new FormRecord(['uid' => $uid]);
	}

    /**
     * Get form model by id
     * 
     * @param  int $id
     * @param  bool $throwIfNotFound
     * @return ?FormModel
     */
	public function getById(int $id, bool $throwIfNotFound = false): ?FormModel
	{
		$form = $this->getAll()[$id] ?? null;
        if (!$form and $throwIfNotFound) {
            throw FormException::idNotFound($id);
        }
        return $form;
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
     * @param  FormModel $form
     * @param  array     $fields
     * @param  array     $rules
     * @return bool
     */
	public function save(FormModel $form, array $fields, array $rules): bool
	{
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE, new FormEvent([
                'form' => $form,
                'fields' => $fields,
                'rules' => $rules,
                'isNew' => empty($form->id),
            ]));
        }
        $form->uid = $form->id ? $form->uid : StringHelper::UUID();
        $projectConfig = \Craft::$app->getProjectConfig();
        $configData = $form->getConfig();
        $configPath = self::CONFIG_KEY . '.' . $form->uid;
        $projectConfig->set($configPath, $configData);

        if (!$form->id) {
            $form->id = Db::idByUid(FormRecord::tableName(), $form->uid);
        }

        Formidable::$plugin->fields->deleteMany($this->gatherFieldsToDelete($form, $fields));
        Formidable::$plugin->fields->saveMany($fields, $form);
        Formidable::$plugin->rules->deleteMany($this->gatherRulesToDelete($form, $rules));
        Formidable::$plugin->rules->saveMany($rules, $form);

        return true;
	}

    /**
     * Handle a added or modified config
     * 
     * @param ConfigEvent $event
     */
    public function handleChanged(ConfigEvent $event)
    {
        $uid = $event->tokenMatches[0];
        $data = $event->newValue;
        $notifications = Formidable::$plugin->notifications;
        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $form = $this->getRecordByUid($uid);
            $isNew = $form->getIsNewRecord();

            $form->name = $data['name'];
            $form->handle = $data['handle'];
            $form->notifyAdmin_id = $data['notifyAdmin'] ? $notifications->getRecordByUid($data['notifyAdmin'])->id : null;
            $form->notify_id = $data['notify'] ? $notifications->getRecordByUid($data['notify'])->id : null;
            $form->notifySpam_id = $data['notifySpam'] ? $notifications->getRecordByUid($data['notifySpam'])->id : null;
            $form->notifyField = $data['notifyField'];
            $form->notifyEmails = $data['notifyEmails'];
            $form->notifySpamEmails = $data['notifySpamEmails'];
            $form->honeypot = $data['honeypot'];
            $form->honeypotField = $data['honeypotField'];
            $form->saveSubmissions = $data['saveSubmissions'];
            $form->saveSpam = $data['saveSpam'];
            $form->redirect = $data['redirect'];
            $form->class = $data['class'];
            $form->method = $data['method'];
            $form->submitLabel = $data['submitLabel'];
            $form->color = $data['color'];
            $form->captcha = $data['captcha'];
            $form->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Clear caches
        $this->forms = null;

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new FormEvent([
                'form' => $form->toModel(),
                'isNew' => $isNew,
            ]));
        }
    }

    /**
     * Validate form data
     * 
     * @param  array  $data [description]
     * @return FormModel
     */
    public function validateData(array $data): FormModel
    {
        if (isset($data['id']) and is_numeric($data['id'])) {
            $form = $this->getById($data['id']);
        } else {
            $form = new FormModel();
        }
        $form->setAttributes($data);
        $form->validate();
        return $form;
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
     * Gathers all the validation errors for a form
     * 
     * @param  Form   $form
     * @return array
     */
    public function gatherErrors(FormModel $form, array $fields, array $rules): array
    {
        $errors = [];
        if ($errors = $form->getErrors()) {
            $errors['form']['errors'] = $errors;
        }
        foreach ($fields as $index => $field) {
            if ($errors = $field->getErrors()) {
                $errors['fields'][$index] = $errors;
            } 
        }
        foreach ($rules as $index => $rule) {
            if ($errors = $rule->getErrors()) {
                $errors['rules'][$index]['errors'] = $errors;
            }
            foreach ($rule->conditions as $index2 => $condition) {
                if ($errors = $condition->getErrors()) {
                    $errors['rules'][$index]['conditions'][$index2] = $errors;
                }
            }
            foreach ($rule->conditionGroups as $index2 => $group) {
                if ($errors = $group->getErrors()) {
                    $errors['rules'][$index]['conditionsGroups'][$index2]['errors'] = $errors;
                    foreach ($group->condition as $index3 => $condition) {
                        if ($errors = $condition->getErrors()) {
                            $errors['rules'][$index]['conditionsGroups'][$index2]['conditions'][$index3] = $errors;
                        }
                    }
                }
            }
        }
        return $errors;
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
            ->delete(FormRecord::tableName(), ['uid' => $uid])
            ->execute();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE, new FormEvent([
                'form' => $form,
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
        foreach ($this->getAll() as $form) {
            $e->config[Formidable::CONFIG_KEY][self::CONFIG_KEY][$form->uid] = $form->getConfig();
        }
    }

    /**
     * Get success status for a form from session
     * 
     * @param string $formHandle
     * @return bool
     */
    public function getSessionSuccess(string $formHandle): bool
    {
        return \Craft::$app->session->getFlash(self::SESSION_SUCCESS_KEY.'.'.$formHandle, false, true);
    }

    /**
     * Get errors for a form from session
     * 
     * @param string $formHandle
     * @return array
     */
    public function getSessionErrors(string $formHandle): array
    {
        return \Craft::$app->session->getFlash(self::SESSION_ERRORS_KEY.'.'.$formHandle, [], true);
    }

    /**
     * Get old input for a form from session
     * 
     * @param string $formHandle
     * @return array
     */
    public function getSessionOldInput(string $formHandle): array
    {
        return \Craft::$app->session->getFlash(self::SESSION_OLDINPUT_KEY.'.'.$formHandle, [], true);

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

        $values = [];
        foreach ($form->getFields() as $field) {
            $values[$field->name] = $posted[$field->name] ?? null;
        }
        $session->setFlash(self::SESSION_OLDINPUT_KEY.'.'.$form->handle, $values);
        $formSubmission = new FormSubmission($values);
        $formSubmission->setForm($form);
        $formSubmission->setRules(Formidable::$plugin->rules->getFormRules($form));
        
        foreach ($form->fields as $field) {
            $field->beforeSubmissionValidated($formSubmission);
        }

        if ($form->honeypot) {
            if (isset($posted[$form->honeypotField]) and !empty($posted[$form->honeypotField])) {
                $spam = Formidable::$plugin->spam->create($form, $formSubmission, $posted);
                return self::SPAM;
            }
        }

        if ($form->captcha) {
            if (!$form->getCaptcha()->verify($form, $formSubmission, $posted)) {
                $spam = Formidable::$plugin->spam->create($form, $formSubmission, $posted);
                return self::SPAM;
            }
        }

        $this->trigger(self::EVENT_BEFORE_SUBMITTING, new SubmissionEvent([
            'submission' => $formSubmission,
            'form' => $form
        ]));

        $formSubmission->validate(null, false);

        if (!$errors = $formSubmission->getErrors()) {
            foreach ($form->fields as $field) {
                $field->afterSubmissionValidated($formSubmission);
            }
            $submission = Formidable::$plugin->submissions->create($form, $formSubmission);
            $session->setFlash(self::SESSION_SUCCESS_KEY.'.'.$form->handle, true);
            $this->trigger(self::EVENT_AFTER_SUBMITTING, new SubmissionEvent([
                'submission' => $submission,
                'form' => $form
            ]));
            return self::VALID;
        }
        $session->setFlash(self::SESSION_ERRORS_KEY.'.'.$form->handle, $errors);
        return self::INVALID;
    }

    /**
     * Sort out fields to delete
     * 
     * @param  FormModel $form
     * @param  array     $fields
     * @return array
     */
    protected function gatherFieldsToDelete(FormModel $form, array $fields): array
    {
        $allFields = Formidable::$plugin->fields->getFormFields($form);
        $toDelete = [];
        foreach ($allFields as $field) {
            if (!ArrayHelper::contains($fields, 'id', $field->id)) {
                $toDelete[] = $field;
            }
        }
        return $toDelete;
    }

    /**
     * Sort out rules to delete
     * 
     * @param  FormModel $form
     * @param  array     $rules
     * @return array
     */
    protected function gatherRulesToDelete(FormModel $form, array $rules): array
    {
        $allRules = Formidable::$plugin->rules->getFormRules($form);
        $toDelete = [];
        foreach ($allRules as $rule) {
            if (!ArrayHelper::contains($rules, 'id', $rule->id)) {
                $toDelete[] = $rule;
            }
        }
        return $toDelete;
    }
}