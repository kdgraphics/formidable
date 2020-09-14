<?php 

namespace Inspire\Forms\Models;

use craft\base\Model;

class Condition extends Model
{
	public $id;
	public $conditiongroup_id;
	public $rule_id;
	public $type;
	public $negate;
	public $operator;
	public $value;
	public $field;
	public $dateCreated;
	public $dateUpdated;
	public $uid;

	public static function validTypes(): array
	{
		return [
			'present' => [
				'label' => \Craft::t('inspireforms', 'is present'),
				'requiresValue' => false,
				'requiresField' => false
			], 
			'smallerThan' => [
				'label' => \Craft::t('inspireforms', 'is smaller than'),
				'requiresValue' => true,
				'requiresField' => false
			],
			'biggerThan' => [
				'label' => \Craft::t('inspireforms', 'is bigger than'),
				'requiresValue' => true,
				'requiresField' => false
			], 
			'equal' => [
				'label' => \Craft::t('inspireforms', 'is equal to'),
				'requiresValue' => true,
				'requiresField' => false
			],
			'sameThan' => [
				'label' => \Craft::t('inspireforms', 'is same than'),
				'requiresValue' => false,
				'requiresField' => true
			],
			'contains' => [
				'label' => \Craft::t('inspireforms', 'contains'),
				'requiresValue' => true,
				'requiresField' => false
			],
			'is on' => [
				'label' => \Craft::t('inspireforms', 'is on'),
				'requiresValue' => false,
				'requiresField' => false
			],
			'is off' => [
				'label' => \Craft::t('inspireforms', 'is off'),
				'requiresValue' => false,
				'requiresField' => false
			]
		];
	}

	public function rules()
	{
		return [
			[['type', 'negate', 'operator', 'field'], 'required'],
			[['type', 'operator', 'field', 'value'], 'string'],
			['type', 'in', 'range' => $this::validTypes()],
			['negate', 'boolean'],
			['operator', 'in', 'range' => ['and', 'or']]
		];
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
			'negate' => $this->negate,
			'operator' => $this->operator,
			'value' => $this->value,
			'field' => $this->field
		];
	}
}