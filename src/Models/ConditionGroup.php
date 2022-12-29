<?php 

namespace Inspire\Formidable\Models;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\FormPosted;
use craft\base\Model;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;

class ConditionGroup extends Model
{
    public $id;
    public $rule_id;
    public $conditions;
    public $negate = false;
    public $operator;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    /**
     * @inheritDoc
     */
    public function afterValidate()
    {
        foreach ($this->conditions as $condition) {
            $condition->validate();
        }
        parent::afterValidate();
    }

    /**
     * @inheritDoc
     */
    public function hasErrors($attribute = null)
    {
        if (parent::hasErrors($attribute)) {
            return true;
        }
        foreach ($this->conditions as $condition) {
            if ($condition->hasErrors()) {
                return true;
            }
        }
        return false;
    }

    public function defineRules(): array
    {
        return [
            ['negate', 'boolean', 'trueValue' => true, 'falseValue' => false],
            ['operator', 'in', 'range' => ['and', 'or']]
        ];
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return array_merge(parent::fields(), ['errors']);
    }

    /**
     * Project config to be saved
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'negate' => $this->negate,
            'operator' => $this->operator,
            'uid' => $this->uid ?? StringHelper::UUID(),
            'conditions' => array_map(function ($condition) {
                return $condition->getConfig();
            }, $this->conditions),
        ];
    }

    public function appliesTo(FormPosted $posted, bool $result)
    {
        $thisResult = true;
        foreach ($this->conditions as $condition) {
            $thisResult = $condition->appliesTo($posted, $thisResult);
        }
        if ($this->negate) {
            $thisResult = !$thisResult;
        }
        if ($this->operator == 'and') {
            return ($thisResult and $result);
        }
        return ($thisResult or $result);
    }
}