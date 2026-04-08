<?php

namespace Inspire\Formidable\services;

use Inspire\Formidable\events\FormEvent;
use Inspire\Formidable\events\FormSubmissionEvent;
use Inspire\Formidable\events\HookAssetsEvent;
use Inspire\Formidable\exceptions\FormException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\models\Form as FormModel;
use Inspire\Formidable\models\Records\Form as FormRecord;
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

    const EVENT_BEFORE_VALIDATING_SUBMISSION = 'before_validating';
    const EVENT_ON_VALID_SUBMISSION = 'on_valid_submission';
    const EVENT_ON_INVALID_SUBMISSION = 'on_invalid_submission';
    const EVENT_ON_SPAM_SUBMISSION = 'on_spam_submission';

    const SESSION_ERRORS_KEY = 'formidable_errors';
    const SESSION_SUCCESS_KEY = 'formidable_success';
    const SESSION_OLDINPUT_KEY = 'formidable_oldinput';

    const EVENT_HOOK_ASSETS = 'formidable_hook';

    const CONFIG_KEY = 'formidable.forms';

    const FRONT_JS_FILE = __DIR__ . '/../assets/dist/validation.js';

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
     * Has the front Js asset been registered
     * @var boolean
     */
    public bool $frontJsRegistered = false;
    private ?FormConfigService $configService = null;
    private ?FormSubmissionService $submissionService = null;
    private ?FormErrorService $errorService = null;

    /**
     * Get all the registered assets. triggers the event for other plugins to register assets
     *
     * @return array
     */
    public function getRegisteredAssets(): array
    {
        if ($this->fieldsAssets == null) {
            $event = new HookAssetsEvent();
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
    public function save(FormModel $form): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE, new FormEvent([
                'form' => $form,
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

        Formidable::$plugin->fields->reset();
        $form->fields = null;
        $form->rules = null;

        return true;
    }

    /**
     * Handle a added or modified config
     *
     * @param ConfigEvent $event
     */
    public function handleChanged(ConfigEvent $event)
    {
        [$form, $isNew] = $this->getConfigService()->applyChanged($event);

        // Clear caches
        $this->forms = null;

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new FormEvent([
                'form' => $form->toModel(),
                'isNew' => $isNew,
            ]));
        }
    }

    public function populateFromData(array $data): FormModel
    {
        if (isset($data['id']) and is_numeric($data['id'])) {
            $form = $this->getById($data['id']);
        } else {
            $form = new FormModel();
        }
        $form->setAttributes($data);
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
    public function gatherErrors(FormModel $form): array
    {
        return $this->getErrorService()->gatherErrors($form);
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
    public function rebuildConfig(RebuildConfigEvent $event)
    {
        foreach ($this->getAll() as $form) {
            $parts = explode('.', self::CONFIG_KEY);
            $event->config[$parts[0]][$parts[1]][$form->uid] = $form->getConfig();
        }
    }

    /**
     * Get success status for a form from session
     *
     * @param string $formHandle
     * @return bool
     */
    public function hasSessionSuccess(string $formHandle): bool
    {
        return \Craft::$app->session->getFlash(self::SESSION_SUCCESS_KEY . '.' . $formHandle, false, true);
    }

    /**
     * Get errors for a form from session
     *
     * @param string $formHandle
     * @return array
     */
    public function getSessionErrors(string $formHandle): array
    {
        return \Craft::$app->session->getFlash(self::SESSION_ERRORS_KEY . '.' . $formHandle, [], true);
    }

    /**
     * Get old input for a form from session
     *
     * @param string $formHandle
     * @return array
     */
    public function getSessionOldInput(string $formHandle): array
    {
        return \Craft::$app->session->getFlash(self::SESSION_OLDINPUT_KEY . '.' . $formHandle, [], true);
    }

    /**
     * Enables the front javascript, registers all registered rules
     */
    public function enableFrontJs()
    {
        $this->getSubmissionService()->enableFrontJs();
    }

    /**
     * Submit a form
     *
     * @param  FormModel $form
     * @return int
     */
    public function submit(FormModel $form): int
    {
        [$result, $submission, $posted] = $this->getSubmissionService()->submit($form);
        if ($result === self::INVALID) {
            $type = self::EVENT_ON_INVALID_SUBMISSION;
        } elseif ($result === self::SPAM) {
            $type = self::EVENT_ON_SPAM_SUBMISSION;
        } else {
            $type = self::EVENT_ON_VALID_SUBMISSION;
        }
        $event = new FormSubmissionEvent([
            'form' => $form,
            'status' => $result,
            'submission' => $submission,
            'posted' => $posted
        ]);
        $this->trigger($type, $event);
        return $result;
    }

    private function getConfigService(): FormConfigService
    {
        if ($this->configService === null) {
            $this->configService = new FormConfigService($this);
        }

        return $this->configService;
    }

    private function getSubmissionService(): FormSubmissionService
    {
        if ($this->submissionService === null) {
            $this->submissionService = new FormSubmissionService($this);
        }

        return $this->submissionService;
    }

    private function getErrorService(): FormErrorService
    {
        if ($this->errorService === null) {
            $this->errorService = new FormErrorService();
        }

        return $this->errorService;
    }
}
