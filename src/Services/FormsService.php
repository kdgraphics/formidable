<?php
namespace Ryssbowh\Formidable\Services;

use Inspire\SpamCheck\SpamCheck;
use Ryssbowh\Formidable\Events\FormEvent;
use Ryssbowh\Formidable\Events\FormSubmissionEvent;
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
use yii\web\View;

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

    const FRONT_JS_FILE = __DIR__ . '/../Assets/dist/validation.js';

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
    protected $frontJsRegistered = false;

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
        $uid = $event->tokenMatches[0];
        $data = $event->newValue;
        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $form = $this->getRecordByUid($uid);
            $isNew = $form->getIsNewRecord();

            $form->name = $data['name'];
            $form->handle = $data['handle'];
            $form->notifyAdmin_key = $data['notifyAdmin_key'];
            $form->notify_key = $data['notify_key'];
            $form->notifySpam_key = $data['notifySpam_key'];
            $form->notifyField = $data['notifyField'];
            $form->notifyEmails = $data['notifyEmails'];
            $form->notifySpamEmails = $data['notifySpamEmails'];
            $form->honeypot = $data['honeypot'];
            $form->honeypotField = $data['honeypotField'];
            $form->saveSubmissions = $data['saveSubmissions'];
            $form->frontEndValidation = $data['frontEndValidation'];
            $form->saveSpam = $data['saveSpam'];
            $form->spamCheck = $data['spamCheck'] ?? '';
            $form->redirect = $data['redirect'];
            $form->class = $data['class'];
            $form->htmlId = $data['htmlId'];
            $form->method = $data['method'];
            $form->submitLabel = $data['submitLabel'];
            $form->submitClass = $data['submitClass'];
            $form->obfuscateFieldNames = $data['obfuscateFieldNames'] ?? false;
            $form->color = $data['color'];
            $form->captcha = $data['captcha'];
            $form->save(false);

            $fieldIds = $ruleIds = [];
            foreach ($data['fields'] ?? [] as $index => $fieldData) {
                $fieldData['order'] = $index;
                $field = $this->handleFieldChange($form, $fieldData);
                $fieldIds[] = $field->id;
                if ($field->type == 'group') {
                    foreach ($fieldData['fields'] ?? [] as $index => $fieldData) {
                        $fieldData['group_id'] = $field->id;
                        $fieldData['order'] = $index;
                        $field2 = $this->handleFieldChange($form, $fieldData);
                        $fieldIds[] = $field2->id;
                    }
                }
            }

            foreach ($data['rules'] ?? [] as $ruleData) {
                $rule = Formidable::$plugin->rules->getRecordByUid($ruleData['uid']);
                $rule->form_id = $form->id;
                $rule->type = $ruleData['type'];
                $rule->title = $ruleData['title'] ?? '';
                $rule->message = $ruleData['message'];
                $rule->messages = $ruleData['messages'] ?? '{}';
                $rule->value = $ruleData['value'];
                $rule->fields = $ruleData['fields'];
                $rule->save(false);
                $ruleIds[] = $rule->id;

                $groupIds = [];
                foreach ($ruleData['conditionGroups'] ?? [] as $groupData) {
                    $group = Formidable::$plugin->conditionGroups->getRecordByUid($groupData['uid']);
                    $group->rule_id = $rule->id;
                    $group->operator = $groupData['operator'];
                    $group->negate = $groupData['negate'];
                    $group->save(false);
                    $groupIds[] = $group->id;
                    $conditionIds = [];
                    foreach ($groupData['conditions'] ?? [] as $conditionData) {
                        $condition = Formidable::$plugin->conditions->getRecordByUid($conditionData['uid']);
                        $condition->conditiongroup_id = $group->id;
                        $condition->type = $conditionData['type'];
                        $condition->negate = $conditionData['negate'];
                        $condition->operator = $conditionData['operator'];
                        $condition->value = $conditionData['value'];
                        $condition->field = $conditionData['field'];
                        $condition->save(false);
                        $conditionIds[] = $condition->id;
                    }
                    Formidable::$plugin->conditions->deleteForGroup($group->id, $conditionIds);
                }
                Formidable::$plugin->conditionGroups->deleteForRule($rule->id, $groupIds);

                $conditionIds = [];
                foreach ($ruleData['conditions'] ?? [] as $conditionData) {
                    $condition = Formidable::$plugin->conditions->getRecordByUid($conditionData['uid']);
                    $condition->rule_id = $rule->id;
                    $condition->type = $conditionData['type'];
                    $condition->negate = $conditionData['negate'];
                    $condition->operator = $conditionData['operator'];
                    $condition->value = $conditionData['value'];
                    $condition->field = $conditionData['field'];
                    $condition->save(false);
                    $conditionIds[] = $condition->id;
                }
                Formidable::$plugin->conditions->deleteForRule($rule->id, $conditionIds);
            }

            Formidable::$plugin->fields->deleteForForm($form->id, $fieldIds);
            Formidable::$plugin->rules->deleteForForm($form->id, $ruleIds);

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

    protected function handleFieldChange(FormRecord $form, array $fieldData)
    {
        $field = Formidable::$plugin->fields->getRecordByUid($fieldData['uid']);
        $field->group_id = $fieldData['group_id'] ?? null;
        $field->active = $fieldData['active'];
        $field->name = $fieldData['name'];
        $field->handle = $fieldData['handle'];
        $field->form_id = $form->id;
        $field->order = $fieldData['order'];
        $field->type = $fieldData['type'];
        $field->options = $fieldData['options'];
        $field->fieldClass = $fieldData['fieldClass'];
        $field->containerClass = $fieldData['containerClass'];
        $field->labelClass = $fieldData['labelClass'];
        $field->instructions = $fieldData['instructions'];
        $field->save(false);
        return $field;
    }

    /**
     * Validate form data
     * 
     * @param  array  $data [description]
     * @return FormModel
     */
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
        $errors = [];
        if ($errors2 = $form->getErrors()) {
            $errors['form'] = $errors2;
        }
        foreach ($form->fields as $index => $field) {
            if ($errors2 = $field->getErrors()) {
                $errors['fields'][$index] = $errors2;
            } 
        }
        foreach ($form->rules as $index => $rule) {
            if ($errors2 = $rule->getErrors()) {
                $errors['rules'][$index]['errors'] = $errors2;
            }
            foreach ($rule->conditions as $index2 => $condition) {
                if ($errors2 = $condition->getErrors()) {
                    $errors['rules'][$index]['conditions'][$index2] = $errors2;
                }
            }
            foreach ($rule->conditionGroups as $index2 => $group) {
                if ($errors2 = $group->getErrors()) {
                    $errors['rules'][$index]['conditionsGroups'][$index2]['errors'] = $errors2;
                    foreach ($group->condition as $index3 => $condition) {
                        if ($errors2 = $condition->getErrors()) {
                            $errors['rules'][$index]['conditionsGroups'][$index2]['conditions'][$index3] = $errors2;
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
            $parts = explode('.', self::CONFIG_KEY);
            $e->config[$parts[0]][$parts[1]][$form->uid] = $form->getConfig();
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
     * Enables the front javascript, registers all registered rules
     */
    public function enableFrontJs()
    {
        if ($this->frontJsRegistered) {
            return;
        }
        $js = file_get_contents(self::FRONT_JS_FILE);
        $rules = "";
        foreach (Formidable::$plugin->rules->getAllRegistered() as $rule) {
            $rules .= $rule->handle() . ": {
                requiresValue: ".($rule->requiresValue ? 'true' : 'false').",
                requiresField: ".($rule->requiresField ? 'true' : 'false').",
                check: {$rule->checkJs()}
            },
            ";
        }
        $js = str_replace('$RULES$', $rules, $js);
        \Craft::$app->view->registerJs($js, View::POS_END);
        $this->frontJsRegistered = true;
    }

    /**
     * Submit a form
     * 
     * @param  FormModel $form
     * @return int
     */
    public function submit(FormModel $form): int
    {
        list($result, $submission, $posted) = $this->_submit($form);
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

    /**
     * Submit a form
     * 
     * @param  FormModel $form
     * @return int
     */
    protected function _submit(FormModel $form): array
    {
        $session = \Craft::$app->session;
        $posted = \Craft::$app->request->getBodyParams();

        //Cleaning up post
        $values = [];
        foreach ($form->fields as $field) {
            //This is needed in case field names are obfuscated
            $postedValue = $posted[$field->frontName] ?? null;
            unset($posted[$field->frontName]);
            $posted[$field->handle] = $postedValue;
            $values[$field->handle] = $postedValue;
        }
        if ($form->honeypot) {
            if ($form->obfuscateFieldNames) {
                $honeypotField = $form->obfuscateFieldName($form->honeypotField);
                $posted[$form->honeypotField] = $posted[$honeypotField] ?? 'missing honeypot from post';
                unset($posted[$honeypotField]);
            } else {
                $posted[$form->honeypotField] = $posted[$form->honeypotField] ?? 'missing honeypot from post';
            }
        }
            
        //Set old input in session
        $session->setFlash(self::SESSION_OLDINPUT_KEY.'.'.$form->handle, $values);
        //Create form submission and assign rules
        $formSubmission = new FormSubmission($values);
        $formSubmission->setForm($form);
        $formSubmission->setRules(Formidable::$plugin->rules->getFormRules($form));
        
        foreach ($form->fields as $field) {
            $field->beforeSubmissionValidated($formSubmission);
        }

        //Check honeypot
        if ($form->honeypot) {
            if (!empty(trim($posted[$form->honeypotField]))) {
                $formSubmission->_spam_reason = \Craft::t('formidable', 'Honeypot was filled');
                $spam = Formidable::$plugin->spam->create($form, $formSubmission, $posted);
                return [self::SPAM, $formSubmission, $posted];
            }
        }

        if ($form->captcha) {
            if (!$form->getCaptcha()->verify($form, $formSubmission, $posted)) {
                $spam = Formidable::$plugin->spam->create($form, $formSubmission, $posted);
                return [self::SPAM, $formSubmission, $posted];
            }
        }

        if ($form->spamCheck and $form->hasField($form->spamCheck)) {
            $res = SpamCheck::$plugin->spamCheck->email(trim($values[$form->spamCheck]));
            if ($res and $res['frequency'] > 10) {
                $formSubmission->_spam_reason = \Craft::t('formidable', 'Known spammer');
                $spam = Formidable::$plugin->spam->create($form, $formSubmission, $posted);
                return [self::SPAM, $formSubmission, $posted];
            }
        }

        //Allow other plugins to set the submission as invalid or spam :
        $event = new FormSubmissionEvent([
            'submission' => $formSubmission,
            'form' => $form,
            'posted' => $posted
        ]);
        $this->trigger(self::EVENT_BEFORE_VALIDATING_SUBMISSION, $event);

        if ($event->status === self::INVALID) {
            return [self::INVALID, $formSubmission, $posted];
        } elseif ($event->status === self::SPAM) {
            return [self::SPAM, $formSubmission, $posted];
        }

        //Validate submission
        $formSubmission->validate(null, false);

        if (!($errors = $formSubmission->getErrors())) {
            foreach ($form->fields as $field) {
                $field->afterSubmissionValidated($formSubmission);
            }
            $submission = Formidable::$plugin->submissions->create($form, $formSubmission);
            $session->setFlash(self::SESSION_SUCCESS_KEY.'.'.$form->handle, true);
            return [self::VALID, $submission, $posted];
        }
        $session->setFlash(self::SESSION_ERRORS_KEY.'.'.$form->handle, $errors);
        return [self::INVALID, $formSubmission, $posted];
    }
}
