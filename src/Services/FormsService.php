<?php
namespace Inspire\Formidable\Services;

use Inspire\SpamCheck\SpamCheck;
use Inspire\Formidable\Events\FormEvent;
use Inspire\Formidable\Events\FormPostedEvent;
use Inspire\Formidable\Events\SpamEvent;
use Inspire\Formidable\Events\SubmissionEvent;
use Inspire\Formidable\Exceptions\FormException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormPosted;
use Inspire\Formidable\records\FormRecord;
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

    const CONFIG_KEY = 'formidable.forms';

    const STATUS_SPAM = 1;
    const STATUS_VALID = 2;
    const STATUS_INVALID = 3;

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
     * FormsPosted objects, keyd by form html ids
     * @var array
     */
    protected $posted = [];

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
     * @return ?Form
     */
    public function getById(int $id, bool $throwIfNotFound = false): ?Form
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
     * @return ?Form
     */
    public function getByHandle(string $handle): ?Form
    {
        return ArrayHelper::firstWhere($this->getAll(), 'handle', $handle);
    }

    /**
     * Get notification model by uid
     * 
     * @param  string $uid
     * @return ?Form
     */
    public function getByUid(string $uid): ?Form
    {
        return ArrayHelper::firstWhere($this->getAll(), 'uid', $uid, true);
    }

    /**
     * Save a form. Adds/Modifies the project config
     * 
     * @param  Form   $form
     * @param  array  $fields
     * @param  array  $rules
     * @return bool
     */ 
    public function save(Form $form): bool
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
                $rule->fields = $ruleData['fields'] ?? [];
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
     * @return Form
     */
    public function populateFromData(array $data): Form
    {
        if (isset($data['id']) and is_numeric($data['id'])) {
            $form = $this->getById($data['id']);
        } else {
            $form = new Form();
        }
        $form->setAttributes($data);
        return $form;
    }

    /**
     * Delete form model
     * 
     * @param  Form $model
     * @return bool
     */
    public function delete(Form $model): bool
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
     * Submit a form
     * 
     * @param  Form  $form
     * @return array
     */
    public function submit(Form $form): array
    {
        list($result, $posted) = $this->_submit($form);
        $submission = null;
        $spam = null;
        if ($result === self::STATUS_INVALID) {
            $type = self::EVENT_ON_INVALID_SUBMISSION;
        } elseif ($result === self::STATUS_SPAM) {
            $spam = Formidable::$plugin->spam->create($posted);
            $type = self::EVENT_ON_SPAM_SUBMISSION;
        } else {
            foreach ($form->fields as $field) {
                $field->afterSubmissionValidated($posted);
            }
            $submission = Formidable::$plugin->submissions->create($posted);
            $type = self::EVENT_ON_VALID_SUBMISSION;
        }
        $event = new FormPostedEvent([
            'form' => $form,
            'status' => $result,
            'submission' => $submission,
            'posted' => $posted,
            'spam' => $spam
        ]);
        $this->trigger($type, $event);
        return [$result, $posted, $submission];
    }

    /**
     * Submit a form
     * 
     * @param  Form $form
     * @return int
     */
    protected function _submit(Form $form): array
    {
        $session = \Craft::$app->session;
        $posted = \Craft::$app->request->getBodyParams();

        //Cleaning up post
        unset($posted['id']);
        unset($posted['action']);
        unset($posted[\Craft::$app->config->general->csrfTokenName]);
        foreach ($form->fields as $field) {
            //This is needed in case field names are obfuscated
            $postedValue = $posted[$field->frontName] ?? null;
            unset($posted[$field->frontName]);
            $posted[$field->handle] = $postedValue;
        }

        //Cleaning honeypot
        if ($form->honeypot) {
            if ($form->obfuscateFieldNames) {
                $honeypotField = $form->obfuscateFieldName($form->honeypotField);
                $value = $posted[$honeypotField] ?? null;
                unset($posted[$honeypotField]);
            } else {
                $value = $posted[$form->honeypotField] ?? null;
            }
            if ($value !== null) {
                $posted[$form->honeypotField] = $value;
            }
        }

        //Create form posted object and save it on this service for later
        $posted = new FormPosted($posted);
        $posted->form = $form;
        $this->setLatestPosted($posted);
        
        foreach ($form->fields as $field) {
            $field->beforeSubmissionValidated($posted);
        }

        //Check captcha
        if ($form->captcha) {
            if (!$form->getCaptcha()->verify($form, $posted)) {
                return [self::STATUS_SPAM, $posted];
            }
        }

        //Check honeypot
        if ($form->honeypot) {
            $isSpam = false;
            if (($posted[$form->honeypotField] ?? null) === null) {
                $isSpam = true;
                $reason = \Craft::t('formidable', 'Honeypot is missing from post');
            } else if (!empty(trim($posted[$form->honeypotField]))) {
                $reason = \Craft::t('formidable', 'Honeypot was filled');
                $isSpam = true;
            }
            if ($isSpam) {
                $posted->spam_reason = $reason;
                $posted->addError('*', \Craft::t('formidable', 'Our spam protection software has detected a high probability that this is a spam or bot submission. If this is a real enquiry please try another browser just in case there is a problem with this one for you.'));
                return [self::STATUS_SPAM, $posted];
            }
        }

        if ($form->spamCheck and $form->hasField($form->spamCheck) and \Craft::$app->plugins->isPluginEnabled('spam-check')) {
            $res = SpamCheck::$plugin->spamCheck->email(trim($posted[$form->spamCheck]));
            if ($res and $res['frequency'] > Formidable::$plugin->settings->spamFrequency) {
                $posted->spam_reason = \Craft::t('formidable', 'Known spammer');
                $posted->addError('*', \Craft::t('formidable', 'Our spam protection software has detected a high probability that this is a spam or bot submission. If this is a real enquiry please try another browser just in case there is a problem with this one for you.'));
                return [self::STATUS_SPAM, $posted];
            }
        }

        //Allow other plugins to set the submission as invalid or spam :
        $event = new FormPostedEvent([
            'form' => $form,
            'posted' => $posted
        ]);
        $this->trigger(self::EVENT_BEFORE_VALIDATING_SUBMISSION, $event);
        if ($event->status === self::STATUS_INVALID or $event->status === self::STATUS_SPAM) {
            return [$event->status, $posted];
        }

        //Validate submission
        $posted->validate(null, false);
        if (!($errors = $posted->getErrors())) {
            return [self::STATUS_VALID, $posted];
        }
        return [self::STATUS_INVALID, $posted];
    }

    public function setLatestPosted(FormPosted $posted)
    {
        $this->posted[$posted->form->generateId()] = $posted;
    }

    public function getLatestPosted(Form $form): ?FormPosted
    {
        return $this->posted[$form->generateId()] ?? null;
    }
}
