<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Events\FieldEvent;
use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\FieldInterface;
use Inspire\Forms\Models\FormSubmission;
use craft\base\Model;
use craft\helpers\Json;
use craft\helpers\StringHelper;

abstract class Field extends Model implements FieldInterface
{
	const EVENT_RENDER = 'render';
	public $id;
	public $name;
	public $order;
	public $fieldClass;
	public $containerClass;
	public $instructions;
	public $form_id;
	private $options;
	public $type;
	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;
	public $uid;

	protected $rootAttributes = ['id', 'name', 'order', 'form_id', 'options', 'dateCreated', 'dateUpdated', 'dateDeleted', 'uid', 'fieldClass', 'containerClass', 'instructions'];

	public function rules()
	{
		return array_merge($this->getExtraCpRules(), [
			[['id'], 'integer'],
			['name', 'required'],
			[['name', 'fieldClass', 'containerClass', 'instructions'], 'string'],
			[['name', 'fieldClass', 'containerClass', 'instructions'], 'trim']
		]);
	}

	/**
	 * inheritDoc
	 **/
	public function frontendTemplate(): string
	{
		return 'inspireforms/fields/'.$this->handle();
	}

	public function getForm()
	{
		return Forms::$plugin->forms->getById($this->form_id);
	}

	public function setOptions($value)
	{
		$this->options = decodeJson($value, true);
		foreach($this->options as $name => $value) {
			$this->$name = $value;
		}
	}

	public function generateId(Form $form)
	{
		return $form->handle.'-field-'.$this->name;
	}

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
            'form_id' => $this->form_id,
            'order' => $this->order,
            'fieldClass' => $this->fieldClass,
            'containerClass' => $this->containerClass,
            'instructions' => $this->instructions,
            'type' => $this->handle(),
            'options' => $this->rebuildOptions(true),
            'uid' => $this->uid
		];
	}
}