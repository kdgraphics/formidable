<?php

namespace Inspire\Formidable\services;

use Inspire\Formidable\events\ConditionGroupEvent;
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

class ConditionGroupsService extends Component
{
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
     * Delete many groups for a rule
     *
     * @param  int    $ruleId
     * @param  array  $keepIds
     */
    public function deleteForRule(int $ruleId, array $keepIds)
    {
        $groups = ConditionGroupRecord::find()->where(['rule_id' => $ruleId])
            ->andWhere(['not in', 'id', $keepIds])->all();
        foreach ($groups as $group) {
            $group->delete();
        }
    }

    /**
     * Validate some conditions groups data
     *
     * @param  array  $groupsData
     * @return array
     */
    public function populateFromData(array $groupsData): array
    {
        $groups = [];
        foreach ($groupsData as $index => $data) {
            if (isset($data['id']) and is_numeric($data['id'])) {
                $group = $this->getById($data['id']);
            } else {
                $group = new ConditionGroup();
            }
            $group->setAttributes($data);
            $group->conditions = Formidable::$plugin->conditions->populateFromData($data['conditions']);
            $groups[$index] = $group;
        }
        return $groups;
    }
}
