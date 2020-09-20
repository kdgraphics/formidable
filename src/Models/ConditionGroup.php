<?php 

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\FormSubmission;
use craft\base\Model;
use craft\helpers\ArrayHelper;

class ConditionGroup extends Model
{
	public $id;
	public $rule_id;
	public $conditions;
	public $negate;
	public $operator;
	public $dateCreated;
	public $dateUpdated;
	public $uid;

	public function rules()
	{
		return [
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
			'negate' => $this->negate,
			'operator' => $this->operator
		];
	}

	public function appliesTo(FormSubmission $submission, bool $result)
	{
		$thisResult = true;
		foreach ($this->conditions as $condition) {
			$thisResult = $condition->appliesTo($submission, $thisResult);
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