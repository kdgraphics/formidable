<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\RuleInterface;
use Inspire\Forms\Models\Form;
use craft\base\Model;
use craft\helpers\ArrayHelper;

abstract class Rule extends Model implements RuleInterface
{
	public $requiresValue = true;

	public $id;
	public $type;
	public $name;
	public $message;
	public $value;
	public $form_id;
	public $fields = [];
	public $defaultMessage;
	public $conditions = [];
	public $conditionGroups = [];
	public $dateCreated;
	public $dateUpdated;
	public $uid;

	public function init()
	{
		parent::init();
		$this->type = $this->handle();
		$this->name = $this->name();
		$this->defaultMessage = $this->defaultMessage();
		$this->message = $this->defaultMessage();
	}

	public function rules()
	{
		return [
			[['message'], 'string'],
			[['value'], 'number'],
			['type', function () {
				if (!Forms::$plugin->rules->getRegistered($this->type)) {
					$this->addError('type', \Craft::t('inspireforms', '{type} is not a valid rule type', ['type' => $this->type]));
				}
			}],
			['fields', 'each', 'rule' => ['string']]
		];
	}

	/**
	 * Get rule's form
	 * 
	 * @return Form
	 */
	public function getForm(): Form
	{
		return Forms::$plugin->forms->getById($this->form_id);
	}

	/**
	 * Get rule's fields
	 * 
	 * @return array
	 */
	public function getFields(): array
	{
		$fields = $this->fields;
		return array_filter($this->getForm()->fields, function ($field) use ($fields) {
			return in_array($field->name, $fields);
		});
	}

	/**
	 * Project config to be saved
	 * 
	 * @return array
	 */
	public function getConfig(): array
	{
		return [
			'type' => $this->type,
            'message' => $this->message,
            'value' => $this->value,
            'fields' => $this->fields
		];
	}

	/**
     * Sort out conditions to delete
     * 
     * @return array
     */
    protected function gatherConditionsToDelete(): array
    {
        $allConditions = Forms::$plugin->rules->getRuleConditions($this);
        $toDelete = [];
        foreach ($allConditions as $condition) {
            if (!ArrayHelper::contains($this->conditions, 'id', $condition->id)) {
                $toDelete[] = $condition;
            }
        }
        return $toDelete;
    }

    /**
     * Sort out rules to delete
     * 
     * @return array
     */
    protected function gatherGroupsToDelete(): array
    {
        $allGroups = Forms::$plugin->rules->getRuleConditionGroups($this);
        $toDelete = [];
        foreach ($allGroups as $group) {
            if (!ArrayHelper::contains($this->conditionGroups, 'id', $group->id)) {
                $toDelete[] = $group;
            }
        }
        return $toDelete;
    }
}