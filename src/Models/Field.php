<?php 

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Events\FieldEvent;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Interfaces\FieldTypeInterface;
use Ryssbowh\Formidable\Models\FieldTypes\TextField;
use Ryssbowh\Formidable\Models\FormSubmission;
use craft\base\Model;
use craft\helpers\StringHelper;

abstract class Field extends Model implements FieldInterface
{
	const EVENT_RENDER = 'render';

	protected $fieldTypeClass = TextField::class;

	public $id;
	public $name;
	public $handle;
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
	public $validRules = [];

	protected $_value;
	protected $_frontName;
	protected $options;

	protected $rootAttributes = ['id', 'name', 'handle', 'order', 'form_id', 'options', 'dateCreated', 'dateUpdated', 'uid', 'fieldClass', 'containerClass', 'labelClass', 'instructions', 'type', 'friendlyName', 'validRules', 'value'];

	/**
	 * inheritDoc
	 **/
	public function init()
	{
		parent::init();
		$this->type = $this->handle();
		$this->friendlyName = $this->friendlyName();
		$this->validRules = $this->fieldType()->validRuleTypes();
	}

	/**
	 * inheritDoc
	 **/
	public function getValue()
	{
		return $this->_value;
	}

	/**
	 * inheritDoc
	 **/
	public function setValue($value)
	{
		$this->_value = $value;
	}

	/**
	 * inheritDoc
	 **/
	public function fieldType(): FieldTypeInterface
	{
		return $this->fieldTypeClass::getInstance();
	}

	/**
	 * inheritDoc
	 **/
	public function rules()
	{
		return array_merge($this->getExtraCpRules(), [
			[['id', 'order'], 'integer'],
			[['name', 'handle'], 'required'],
			[['name', 'fieldClass', 'containerClass', 'instructions', 'labelClass', 'handle'], 'string'],
			[['name', 'fieldClass', 'containerClass', 'instructions', 'labelClass', 'handle'], 'trim']
		]);
	}

	/**
	 * inheritDoc
	 **/
	public function frontendTemplate(): string
	{
		return 'formidable/fields/' . $this->handle();
	}

	/**
	 * Get form model
	 * 
	 * @return ?Form
	 */
	public function getForm(): ?Form
	{
		return Formidable::$plugin->forms->getById($this->form_id);
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
		return $form->handle.'-field-'.$this->handle;
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
	 * @inheritDoc
	 */
	public function getFrontName(): string
	{
		if ($this->_frontName === null) {
			$this->_frontName = $this->handle;
			if ($this->form->obfuscateFieldNames) {
				$this->_frontName = $this->form->obfuscateFieldName($this->handle);
			}
		}
		return $this->_frontName;
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
			'handle' => $this->handle,
            'order' => $this->order,
            'fieldClass' => $this->fieldClass,
            'containerClass' => $this->containerClass,
            'labelClass' => $this->labelClass,
            'instructions' => $this->instructions,
            'type' => $this->handle(),
            'options' => $this->rebuildOptions(true),
            'uid' => $this->uid ?? StringHelper::UUID()
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
	public static function getHtmlValue($value): string
	{
		return (string)$value;
	}

	/**
	 * @inheritDoc
	 */
	public static function getStringValue($value): string
	{
		return (string)$value;
	}

	/**
	 * @inheritDoc
	 */
	public static function getCsvValue($value): string
	{
		return (string)$value;
	}

	/**
	 * Javascript to define this field
	 * 
	 * @return string
	 */
	public function frontJs(Form $form): string
	{
		return "new formidable.field({
			name: '".addslashes($this->name)."',
			handle: '{$this->handle}',
			type: '{$this->fieldType()->jsType()}',
			id: '{$this->generateId($form)}',
			formId: '{$form->generateId()}'
		})";
	}
}
