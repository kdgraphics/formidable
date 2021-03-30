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