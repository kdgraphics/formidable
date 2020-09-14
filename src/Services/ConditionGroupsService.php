<?php 

namespace Inspire\Forms\Services;

use Inspire\Forms\Events\ConditionGroupEvent;
use Inspire\Forms\Events\RegisterRulesEvent;
use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\RuleInterface;
use Inspire\Forms\Models\Condition;
use Inspire\Forms\Models\ConditionGroup;
use Inspire\Forms\Models\Form;
use Inspire\Forms\Models\Records\Condition as ConditionRecord;
use Inspire\Forms\Models\Records\ConditionGroup as ConditionGroupRecord;
use Inspire\Forms\Models\Records\Form as FormRecord;
use Inspire\Forms\Models\Records\Rule as RuleRecord;
use Inspire\Forms\Models\Rule;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\helpers\Db;
use craft\helpers\StringHelper;

class ConditionGroupsService extends Component
{
    const EVENT_BEFORE_SAVE = 'before_save';
    const EVENT_AFTER_SAVE = 'after_save';
    const EVENT_BEFORE_APPLY_DELETE = 'before_apply_delete';
    const EVENT_AFTER_DELETE = 'after_delete';
    const EVENT_BEFORE_DELETE = 'before_delete';

    const CONFIG_KEY = Forms::CONFIG_KEY . '.conditionGroups';

    /**
     * Get condition group record by uid
     * 
     * @param  string $uid
     * @return ConditionGroupRecord
     */
    public function getRecordByUid(string $uid): ConditionGroupRecord
    {
        return ConditionGroupRecord::findOne(['uid' => $uid]) ?? new ConditionGroupRecord(['uid' => $uid]);
    }

    /**
     * Get condition group model by id
     * 
     * @param  int $id
     * @return ?ConditionGroup
     */
    public function getById(int $id): ?ConditionGroup
    {
        $group = ConditionGroupRecord::find()->where(['id' => $id])->one();
        return $group ? $group->toModel() : null;
    }

    /**
     * Get group model by uid
     * 
     * @param  string $uid
     * @return ?ConditionGroup
     */
    public function getByUid(string $uid): ?ConditionGroup
    {
        $group = ConditionGroupRecord::find()->where(['uid' => $uid])->one();
        return $group ? $group->toModel() : null;
    }

    /**
     * Save a group. Adds/Modifies the project config
     * 
     * @param  ConditionGroup $group
     * @param  Rule           $rule
     * @return bool
     */
    public function save(ConditionGroup $group, Rule $rule): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE, new ConditionGroupEvent([
                'group' => $group,
                'isNew' => empty($group->id),
            ]));
        }

        $group->uid = $group->id ? $group->uid : StringHelper::UUID();
        $projectConfig = \Craft::$app->getProjectConfig();
        $configData = $group->getConfig();
        $configData['rule'] = $rule->uid;
        $configPath = self::CONFIG_KEY . '.' . $group->uid;
        $projectConfig->set($configPath, $configData);

        if (!$group->id) {
            $group->id = Db::idByUid(ConditionGroupRecord::tableName(), $group->uid);
        }

        foreach ($group->conditions as $condition) {
            Forms::$plugin->conditions->save($condition, $group);
        }

        return true;
    }

    /**
     * Save many condition groups
     * 
     * @param  array  $conditionGroups
     * @param  Rule   $rule
     */
    public function saveMany(array $conditionGroups, Rule $rule)
    {
        foreach ($conditionGroups as $group) {
            $this->save($group, $rule);
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
            $group = $this->getRecordByUid($uid);
            $isNew = $group->getIsNewRecord();
            $group->rule_id = Forms::$plugin->rules->getByUid($data['rule'])->id;
            $group->operator = $data['operator'];
            $group->negate = $data['negate'];
            $group->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new ConditionGroupEvent([
                'group' => $group->toModel(),
                'isNew' => $isNew,
            ]));
        }
    }

    /**
     * Get all conditions groups for a rule
     * 
     * @param  RuleInterface $rule
     * @return array
     */
    public function getRuleConditionGroups(RuleInterface $rule): array
    {
        $groups = [];
        foreach (ConditionGroupRecord::find()->where(['rule_id' => $rule->id])->all() as $group) {
            $groups[$group->id] = $group->toModel();
        }
        return $groups;
    }

    /**
     * Delete condition model
     * 
     * @param  ConditionGroup $model
     * @return bool
     */
    public function delete(ConditionGroup $model): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_DELETE, new ConditionGroupEvent([
                'group' => $model,
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

        $group = $this->getByUid($uid);

        if (!$group) {
            return;
        }

        if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_APPLY_DELETE, new ConditionGroupEvent([
                'group' => $group,
            ]));
        }

        \Craft::$app->getDb()->createCommand()
            ->delete(ConditionGroupRecord::tableName(), ['uid' => $uid])
            ->execute();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE, new ConditionGroupEvent([
                'group' => $group,
            ]));
        }
    }

    /**
     * Delete many conditions
     * 
     * @param  array  $conditions
     */
    public function deleteMany(array $groups)
    {
        foreach ($groups as $group) {
            $this->delete($group);
        }
    }

    /**
     * Validate some conditions groups data
     * 
     * @param  array  $groupsData
     * @return array
     */
    public function validateData(array $groupsData): array
    {
        $groups = [];
        foreach ($groupsData as $index => $data) {
            if (isset($data['id']) and is_numeric($data['id'])) {
                $group = $this->getById($data['id']);
            } else {
                $group = new ConditionGroup;
            }
            $group->setAttributes($data);
            $group->validate();
            $group->conditions = Forms::$plugin->conditions->validateData($data['conditions']);
            $groups[$index] = $group;
        }
        return $groups;
    }
}