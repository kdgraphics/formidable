<?php 

namespace Inspire\Forms\Services;

use Inspire\Forms\Events\FormEvent;
use Inspire\Forms\Events\HookAssetsEvent;
use Inspire\Forms\Events\SpamEvent;
use Inspire\Forms\Events\SubmissionEvent;
use Inspire\Forms\Forms;
use Inspire\Forms\Models\Form as FormModel;
use Inspire\Forms\Models\FormSubmission;
use Inspire\Forms\Models\Records\Form as FormRecord;
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
    const EVENT_ON_SPAM = 'on_spam';

    const SESSION_ERRORS_KEY = 'inspireforms_errors';
    const SESSION_SUCCESS_KEY = 'inspireforms_success';
    const SESSION_OLDINPUT_KEY = 'inspireforms_oldinput';

    const EVENT_HOOK_ASSETS = 'inspireforms_hook';

	const CONFIG_KEY = Forms::CONFIG_KEY . '.forms';

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

        Forms::$plugin->fields->deleteMany($this->gatherFieldsToDelete($form, $fields));
        Forms::$plugin->fields->saveMany($fields, $form);
        Forms::$plugin->rules->deleteMany($this->gatherRulesToDelete($form, $rules));
        Forms::$plugin->rules->saveMany($rules, $form);

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
        $notifications = Forms::$plugin->notifications;
        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $form = $this->getRecordByUid($uid);
            $isNew = $form->getIsNewRecord();

            $form->name = $data['name'];
            $form->handle = $data['handle'];
            $form->notifyAdmin_id = $data['notifyAdmin'] ? $notifications->getRecordByUid($data['notifyAdmin'])->id : null;
            $form->notify_id = $data['notify'] ? $notifications->getRecordByUid($data['notify'])->id : null;
            $form->notifyField = $data['notifyField'];
            $form->notifyEmails = $data['notifyEmails'];
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
            $e->config[Forms::CONFIG_KEY][self::CONFIG_KEY][$form->uid] = $form->getConfig();
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
                $spam = Forms::$plugin->spam->create($form, $posted);
                $this->trigger(self::EVENT_ON_SPAM, new SpamEvent([
                    'spam' => $spam,
                    'form' => $form
                ]));
                return self::SPAM;
            }
        }

        if ($form->captcha) {
            if (!$form->getCaptcha()->verify($form, $posted)) {
                $spam = Forms::$plugin->spam->create($form, $posted);
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
     * Sort out fields to delete
     * 
     * @param  FormModel $form
     * @param  array     $fields
     * @return array
     */
    protected function gatherFieldsToDelete(FormModel $form, array $fields): array
    {
        $allFields = Forms::$plugin->fields->getFormFields($form);
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
        $allRules = Forms::$plugin->rules->getFormRules($form);
        $toDelete = [];
        foreach ($allRules as $rule) {
            if (!ArrayHelper::contains($rules, 'id', $rule->id)) {
                $toDelete[] = $rule;
            }
        }
        return $toDelete;
    }
}