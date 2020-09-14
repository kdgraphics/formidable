<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Events\FieldEvent;
use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\FieldInterface;
use Inspire\Forms\Interfaces\FieldTypeInterface;
use Inspire\Forms\Models\FieldTypes\TextField;
use Inspire\Forms\Models\FormSubmission;
use craft\base\Model;

abstract class Field extends Model implements FieldInterface
{
	const EVENT_RENDER = 'render';

	protected $fieldTypeClass = TextField::class;

	public $id;
	public $name;
	public $friendlyName;
	public $order;
	public $fieldClass;
	public $containerClass;
	public $labelClass;
	public $instructions;
	public $form_id;
	public $type;
	public $dateCreated;
	public $dateUpdated;
	public $uid;
	public $validConditions;
	public $validRules;
	private $options;

	protected $rootAttributes = ['id', 'name', 'order', 'form_id', 'options', 'dateCreated', 'dateUpdated', 'uid', 'fieldClass', 'containerClass', 'instructions', 'type', 'friendlyName', 'validRules', 'validConditions'];

	public function init()
	{
		parent::init();
		$this->type = $this->handle();
		$this->friendlyName = $this->friendlyName();
		$this->validConditions = $this->fieldType()->getValidConditionTypes();
		$this->validRules = $this->fieldType()->getValidRuleTypes();
	}

	public function fieldType(): FieldTypeInterface
	{
		return $this->fieldTypeClass::getInstance();
	}

	public function rules()
	{
		return array_merge($this->getExtraCpRules(), [
			[['id', 'order'], 'integer'],
			['name', 'required'],
			[['name', 'fieldClass', 'containerClass', 'instructions', 'labelClass'], 'string'],
			[['name', 'fieldClass', 'containerClass', 'instructions', 'labelClass'], 'trim']
		]);
	}

	/**
	 * inheritDoc
	 **/
	public function frontendTemplate(): string
	{
		return 'inspireforms/fields/'.$this->handle();
	}

	/**
	 * Get form model
	 * 
	 * @return ?Form
	 */
	public function getForm(): ?Form
	{
		return Forms::$plugin->forms->getById($this->form_id);
	}

	/**
	 * Extract all options in object
	 * 
	 * @param string $value
	 */
	public function setOptions(string $value)
	{
		$this->options = decodeJson($value, true);
		foreach($this->options as $name => $value) {
			$this->$name = $value;
		}
	}

	/**
	 * Unique id for this field
	 * 
	 * @param  Form   $form
	 * @return string
	 */
	public function generateId(Form $form): string
	{
		return $form->handle.'-field-'.$this->name;
	}

	/**
	 * Rebuild options in json/array
	 * 
	 * @param  bool|boolean $asJson
	 * @return string|array
	 */
	public function rebuildOptions(bool $asJson = false)
	{
		$keys = array_diff(array_keys($this->attributes), $this->rootAttributes);
		$this->options = [];
		foreach ($keys as $key) {
			$this->options[$key] = $this->$key;
		}
		if (!$asJson) {
			return $this->options;
		}
		return json_encode($this->options);
	}

	/**
	 * Field project config
	 * 
	 * @return array
	 */
	public function getConfig(): array
	{
		return [
			'name' => $this->name,
            'order' => $this->order,
            'fieldClass' => $this->fieldClass,
            'containerClass' => $this->containerClass,
            'instructions' => $this->instructions,
            'type' => $this->handle(),
            'options' => $this->rebuildOptions(true)
		];
	}

	/**
	 * @inheritDoc
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
	}

	/**
	 * @inheritDoc
	 */
	public function beforeSubmissionValidated(FormSubmission $submission)
	{
	}

	/**
	 * @inheritDoc
	 */
	public static function getSubmissionValue($value)
	{
		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public static function getCsvValue($value)
	{
		return $value;
	}
}