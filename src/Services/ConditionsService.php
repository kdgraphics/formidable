<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Events\ConditionEvent;
use Ryssbowh\Formidable\Events\RegisterRulesEvent;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\RuleInterface;
use Ryssbowh\Formidable\Models\Condition;
use Ryssbowh\Formidable\Models\ConditionGroup;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\Records\Condition as ConditionRecord;
use Ryssbowh\Formidable\Models\Records\ConditionGroup as ConditionGroupRecord;
use Ryssbowh\Formidable\Models\Records\Form as FormRecord;
use Ryssbowh\Formidable\Models\Records\Rule as RuleRecord;
use Ryssbowh\Formidable\Models\Rule;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\helpers\Db;
use craft\helpers\StringHelper;

class ConditionsService extends Component
{
    const EVENT_BEFORE_SAVE = 'before_save';
    const EVENT_AFTER_SAVE = 'after_save';
    const EVENT_BEFORE_APPLY_DELETE = 'before_apply_delete';
    const EVENT_AFTER_DELETE = 'after_delete';
    const EVENT_BEFORE_DELETE = 'before_delete';

    const CONFIG_KEY = Formidable::CONFIG_KEY . '.conditions';

    /**
     * Get condition record by uid
     * 
     * @param  string $uid
     * @return ConditionRecord
     */
    public function getRecordByUid(string $uid): ConditionRecord
    {
        return ConditionRecord::findOne(['uid' => $uid]) ?? new ConditionRecord(['uid' => $uid]);
    }

    /**
     * Get condition model by id
     * 
     * @param  int $id
     * @return ?Condition
     */
    public function getById(int $id): ?Condition
    {
        $condition = ConditionRecord::find()->where(['id' => $id])->one();
        return $condition ? $condition->toModel() : null;
    }

    /**
     * Get condition model by uid
     * 
     * @param  string $uid
     * @return ?FieldInterface
     */
    public function getByUid(string $uid): ?RuleInterface
    {
        $condition = ConditionRecord::find()->where(['uid' => $uid])->one();
        return $condition ? $condition->toModel() : null;
    }

    /**
     * Save a condition. Adds/Modifies the project config
     * 
     * @param  Condition $condition
     * @return bool
     */
    public function save(Condition $condition, $relation): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE, new ConditionEvent([
                'condition' => $condition,
                'isNew' => empty($condition->id),
            ]));
        }

        $condition->uid = $condition->id ? $condition->uid : StringHelper::UUID();
        $projectConfig = \Craft::$app->getProjectConfig();
        $configData = $condition->getConfig();
        if ($relation instanceof Rule) {
            $configData['rule'] = $relation->uid;
        } else {
            $configData['conditiongroup'] = $relation->uid;
        }
        $configPath = self::CONFIG_KEY . '.' . $condition->uid;
        $projectConfig->set($configPath, $configData);

        if (!$condition->id) {
            $condition->id = Db::idByUid(ConditionRecord::tableName(), $condition->uid);
        }

        return true;
    }

    /**
     * Save many conditions
     * 
     * @param  array  $conditions
     * @param  Rule   $rule
     */
    public function saveMany(array $conditions, Rule $rule)
    {
        foreach ($conditions as $condition) {
            $this->save($condition, $rule);
        }
    }

    /**
     * Handle condition project config changes
     */
    public function handleChanged(ConfigEvent $event)
    {
        $uid = $event->tokenMatches[0];
        $data = $event->newValue;
        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $condition = $this->getRecordByUid($uid);
            $isNew = $condition->getIsNewRecord();
            if (isset($data['rule'])) {
                $condition->rule_id = Formidable::$plugin->rules->getByUid($data['rule'])->id;
            } else {
                $condition->conditiongroup_id = Formidable::$plugin->conditionGroups->getByUid($data['conditiongroup'])->id;
            }
            $condition->type = $data['type'];
            $condition->negate = $data['negate'];
            $condition->operator = $data['operator'];
            $condition->value = $data['value'];
            $condition->field = $data['field'];
            $condition->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new ConditionEvent([
                'condition' => $condition->toModel(),
                'isNew' => $isNew,
            ]));
        }
    }

    /**
     * Get all conditions for a rule
     * 
     * @param  RuleInterface $rule
     * @return array
     */
    public function getRuleConditions(RuleInterface $rule): array
    {
        $conditions = [];
        foreach (ConditionRecord::find()->where(['rule_id' => $rule->id])->all() as $condition) {
            $conditions[$condition->id] = $condition->toModel();
        }
        return $conditions;
    }

    /**
     * Get all conditions for a group
     * 
     * @param  ConditionGroup $group
     * @return array
     */
    public function getGroupConditions(ConditionGroup $group): array
    {
        $conditions = [];
        foreach (ConditionRecord::find()->where(['conditiongroup_id' => $group->id])->all() as $condition) {
            $conditions[$condition->id] = $condition->toModel();
        }
        return $conditions;
    }

    /**
     * Delete condition model
     * 
     * @param  Condition $model
     * @return bool
     */
    public function delete(Condition $model): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_DELETE, new ConditionEvent([
                'condition' => $model,
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

        $condition = $this->getByUid($uid);

        if (!$condition) {
            return;
        }

        if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_APPLY_DELETE, new ConditionEvent([
                'condition' => $condition,
            ]));
        }

        \Craft::$app->getDb()->createCommand()
            ->delete(ConditionRecord::tableName(), ['uid' => $uid])
            ->execute();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE, new ConditionEvent([
                'condition' => $condition,
            ]));
        }
    }

    /**
     * Delete many conditions
     * 
     * @param  array  $conditions
     */
    public function deleteMany(array $conditions)
    {
        foreach ($conditions as $condition) {
            $this->delete($condition);
        }
    }

    /**
     * Validate some conditions data
     * 
     * @param  array  $conditionsData
     * @return array
     */
    public function validateData(array $conditionsData): array
    {
        $conditions = [];
        foreach ($conditionsData as $index => $data) {
            if (isset($data['id']) and is_numeric($data['id'])) {
                $condition = $this->getById($data['id']);
            } else {
                $condition = new Condition;
            }
            $condition->setAttributes($data);
            $condition->validate();
            $conditions[$index] = $condition;
        }
        return $conditions;
    }
}