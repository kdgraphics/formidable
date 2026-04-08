<?php

namespace Inspire\Formidable\services;

use Inspire\Formidable\events\RegisterRulesEvent;
use Inspire\Formidable\events\RuleEvent;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\interfaces\RuleInterface;
use Inspire\Formidable\models\Condition;
use Inspire\Formidable\models\ConditionGroup;
use Inspire\Formidable\models\Form;
use Inspire\Formidable\models\FormSubmission;
use Inspire\Formidable\models\Records\Condition as ConditionRecord;
use Inspire\Formidable\models\Records\ConditionGroup as ConditionGroupRecord;
use Inspire\Formidable\models\Records\Form as FormRecord;
use Inspire\Formidable\models\Records\Rule as RuleRecord;
use Inspire\Formidable\models\Rule;
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
            $event = new RegisterRulesEvent();
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
