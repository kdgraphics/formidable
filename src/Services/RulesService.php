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
     * Delete many rules
     * 
     * @param  int    $formId
     * @param  array  $keepIds
     */
    public function deleteForForm(int $formId, array $keepIds)
    {
        $rules = RuleRecord::find()->where(['form_id' => $formId])
            ->andWhere(['not in', 'id', $keepIds])->all();
        foreach ($rules as $rule) {
            $rule->delete();
        }
    }

    /**
     * Validate some rules data
     * 
     * @param  array $rulesData
     * @return array
     */
    public function populateFromData(array $rulesData)
    {
        $rules = [];
        foreach ($rulesData as $index => $data) {
            if (isset($data['id']) and is_numeric($data['id'])) {
                $rule = $this->getById($data['id']);
            } else {
                $rule = $this->getRegistered($data['type']);
            }
            $rule->setAttributes($data);
            $rule->conditions = Formidable::$plugin->conditions->populateFromData($data['conditions']);
            $rule->conditionGroups = Formidable::$plugin->conditionGroups->populateFromData($data['conditionGroups']);
            $rules[$index] = $rule;
        }
        return $rules;
    }
}