<?php

namespace Inspire\Formidable\services;

use Inspire\Formidable\events\ConditionEvent;
use Inspire\Formidable\events\RegisterRulesEvent;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\interfaces\RuleInterface;
use Inspire\Formidable\models\Condition;
use Inspire\Formidable\models\ConditionGroup;
use Inspire\Formidable\models\Form;
use Inspire\Formidable\models\Records\Condition as ConditionRecord;
use Inspire\Formidable\models\Records\ConditionGroup as ConditionGroupRecord;
use Inspire\Formidable\models\Records\Form as FormRecord;
use Inspire\Formidable\models\Records\Rule as RuleRecord;
use Inspire\Formidable\models\Rule;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\helpers\Db;
use craft\helpers\StringHelper;

class ConditionsService extends Component
{
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
     * @return ?Condition
     */
    public function getByUid(string $uid): ?Condition
    {
        $condition = ConditionRecord::find()->where(['uid' => $uid])->one();
        return $condition ? $condition->toModel() : null;
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
     * Delete many conditions fo a rule
     *
     * @param  int    $ruleId
     * @param  array  $keepIds
     */
    public function deleteForRule(int $ruleId, array $keepIds)
    {
        $conds = ConditionRecord::find()->where(['rule_id' => $ruleId])
            ->andWhere(['not in', 'id', $keepIds])->all();
        foreach ($conds as $cond) {
            $cond->delete();
        }
    }

    /**
     * Delete many conditions for a group
     *
     * @param  int    $groupId
     * @param  array  $keepIds
     */
    public function deleteForGroup(int $groupId, array $keepIds)
    {
        $conds = ConditionRecord::find()->where(['conditiongroup_id' => $groupId])
            ->andWhere(['not in', 'id', $keepIds])->all();
        foreach ($conds as $cond) {
            $cond->delete();
        }
    }

    /**
     * Validate some conditions data
     *
     * @param  array  $conditionsData
     * @return array
     */
    public function populateFromData(array $conditionsData): array
    {
        $conditions = [];
        foreach ($conditionsData as $index => $data) {
            if (isset($data['id']) and is_numeric($data['id'])) {
                $condition = $this->getById($data['id']);
            } else {
                $condition = new Condition();
            }
            $condition->setAttributes($data);
            $conditions[$index] = $condition;
        }
        return $conditions;
    }
}
