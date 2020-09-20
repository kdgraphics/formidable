<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Events\RegisterRulesEvent;
use Ryssbowh\Formidable\Events\RuleEvent;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\RuleInterface;
use Ryssbowh\Formidable\Models\Condition;
use Ryssbowh\Formidable\Models\ConditionGroup;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Records\Condition as ConditionRecord;
use Ryssbowh\Formidable\Models\Records\ConditionGroup as ConditionGroupRecord;
use Ryssbowh\Formidable\Models\Records\Form as FormRecord;
use Ryssbowh\Formidable\Models\Records\Rule as RuleRecord;
use Ryssbowh\Formidable\Models\Rule;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\helpers\Db;
use craft\helpers\StringHelper;

class RulesService extends Component
{
    const EVENT_BEFORE_SAVE = 'before_save';
    const EVENT_AFTER_SAVE = 'after_save';
    const EVENT_BEFORE_APPLY_DELETE = 'before_apply_delete';
    const EVENT_AFTER_DELETE = 'after_delete';
    const EVENT_BEFORE_DELETE = 'before_delete';

    const CONFIG_KEY = Formidable::CONFIG_KEY . '.rules';

    const EVENT_REGISTER_RULES = 'register_rules';

    /**
     * Rregistered rules
     * @var array
     */
    protected $registeredRules;

    /**
     * Get all registered fields 
     * 
     * @return array
     */
    public function getAllRegistered(): array
    {
        if (is_null($this->registeredRules)) {
            $event = new RegisterRulesEvent;
            $this->trigger(self::EVENT_REGISTER_RULES, $event); 
            $this->registeredRules = $event->getRules();
        }
        return $this->registeredRules;
    }

    /**
     * Get a registered rule by handle
     * 
     * @param  string $handle
     * @return ?RuleInterface
     */
    public function getRegistered(string $handle): ?RuleInterface
    {
        return $this->getAllRegistered()[$handle] ?? null;
    }

    /**
     * Save a rule. Adds/Modifies the project config
     * 
     * @param  RuleInterface $rule
     * @param  Form          $form
     * @return bool
     */
    public function save(RuleInterface $rule, Form $form): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE, new RuleEvent([
                'rule' => $rule,
                'isNew' => empty($rule->id),
            ]));
        }

        $rule->uid = $rule->id ? $rule->uid : StringHelper::UUID();
        $projectConfig = \Craft::$app->getProjectConfig();
        $configData = $rule->getConfig();
        $configData['form_id'] = $form->uid;
        $configPath = self::CONFIG_KEY . '.' . $rule->uid;
        $projectConfig->set($configPath, $configData);

        if (!$rule->id) {
            $rule->id = Db::idByUid(RuleRecord::tableName(), $rule->uid);
        }

        Formidable::$plugin->conditions->saveMany($rule->conditions, $rule);
        Formidable::$plugin->conditionGroups->saveMany($rule->conditionGroups, $rule);

        return true;
    }

    /**
     * Save many rules
     * 
     * @param  array  $rules
     * @param  Form   $form
     */
    public function saveMany(array $rules, Form $form)
    {
        foreach ($rules as $rule) {
            $this->save($rule, $form);
        }
    }

    /**
     * Handle project config changes
     */
    public function handleChanged(ConfigEvent $event)
    {
        $uid = $event->tokenMatches[0];
        $data = $event->newValue;
        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $rule = $this->getRecordByUid($uid);
            $isNew = $rule->getIsNewRecord();
            $rule->form_id = Formidable::$plugin->forms->getByUid($data['form_id'])->id;
            $rule->type = $data['type'];
            $rule->message = $data['message'];
            $rule->value = $data['value'];
            $rule->fields = $data['fields'];
            $rule->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new RuleEvent([
                'rule' => $rule->toModel(),
                'isNew' => $isNew,
            ]));
        }
    }

    /**
     * Get rule record by uid
     * 
     * @param  string $uid
     * @return FieldRecord
     */
    public function getRecordByUid(string $uid): RuleRecord
    {
        return RuleRecord::findOne(['uid' => $uid]) ?? new RuleRecord(['uid' => $uid]);
    }

    /**
     * Get rule model by id
     * 
     * @param  int $id
     * @return ?FieldInterface
     */
    public function getById(int $id): ?RuleInterface
    {
        $rule = RuleRecord::find()->where(['id' => $id])->one();
        return $rule ? $rule->toModel() : null;
    }

    /**
     * Get rule model by uid
     * 
     * @param  string $uid
     * @return ?FieldInterface
     */
    public function getByUid(string $uid): ?RuleInterface
    {
        $rule = RuleRecord::find()->where(['uid' => $uid])->one();
        return $rule ? $rule->toModel() : null;
    }

    /**
     * Get all rules for a form
     * 
     * @param  Form   $form
     * @return array
     */
    public function getFormRules(Form $form): array
    {
        $rules = [];
        foreach (RuleRecord::find()->where(['form_id' => $form->id])->all() as $rule) {
            $rules[] = $rule->toModel();
        }
        return $rules;
    }

    /**
     * Handle a deleted config
     * 
     * @param ConfigEvent $event
     */
    public function handleDeleted(ConfigEvent $event)
    {
        $uid = $event->tokenMatches[0];

        $rule = $this->getByUid($uid);

        if (!$rule) {
            return;
        }

        if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_APPLY_DELETE, new RuleEvent([
                'rule' => $rule,
            ]));
        }

        \Craft::$app->getDb()->createCommand()
            ->delete(RuleRecord::tableName(), ['uid' => $uid])
            ->execute();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE, new RuleEvent([
                'rule' => $rule,
            ]));
        }
    }

    /**
     * Delete rule model
     * 
     * @param  Rule $model
     * @return bool
     */
    public function delete(Rule $model): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_DELETE, new RuleEvent([
                'rule' => $model,
            ]));
        }
        
        \Craft::$app->getProjectConfig()->remove(self::CONFIG_KEY . '.' . $model->uid);

        return true;
    }

    /**
     * Delete many rules
     * 
     * @param  array  $conditions
     */
    public function deleteMany(array $rules)
    {
        foreach ($rules as $rule) {
            $this->delete($rule);
        }
    }

    /**
     * Validate some rules data
     * 
     * @param  array $rulesData
     * @return array
     */
    public function validateData(array $rulesData)
    {
        $rules = [];
        foreach ($rulesData as $index => $data) {
            if (isset($data['id']) and is_numeric($data['id'])) {
                $rule = $this->getById($data['id']);
            } else {
                $rule = $this->getRegistered($data['type']);
            }
            $rule->setAttributes($data);
            $rule->validate();
            $rule->conditions = Formidable::$plugin->conditions->validateData($data['conditions']);
            $rule->conditionGroups = Formidable::$plugin->conditionGroups->validateData($data['conditionGroups']);
            $rules[$index] = $rule;
        }
        return $rules;
    }
}