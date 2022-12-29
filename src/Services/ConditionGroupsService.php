<?php 

namespace Inspire\Formidable\Services;

use Inspire\Formidable\Events\ConditionGroupEvent;
use Inspire\Formidable\Events\RegisterRulesEvent;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\Interfaces\RuleInterface;
use Inspire\Formidable\Models\Condition;
use Inspire\Formidable\Models\ConditionGroup;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\records\ConditionRecord;
use Inspire\Formidable\records\ConditionGroupRecord;
use Inspire\Formidable\records\FormRecord;
use Inspire\Formidable\records\RuleRecord;
use Inspire\Formidable\Models\Rule;
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
                $group = new ConditionGroup;
            }
            $group->setAttributes($data);
            $group->conditions = Formidable::$plugin->conditions->populateFromData($data['conditions']);
            $groups[$index] = $group;
        }
        return $groups;
    }
}