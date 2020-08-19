<?php 

namespace Inspire\Forms\Models;

use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\FieldInterface;
use craft\base\Model;

abstract class Field extends Model implements FieldInterface
{
	public $id;
	public $name;
	public $handle;
	public $form_id;
	public $options;
	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;
	public $uid;

	public function rules()
	{
		return array_merge($this->getOptionsRules(), [
			[['name', 'handle'], 'string'],
			[['name', 'handle'], 'trim'],
			[['id', 'form_id'], 'integer']
		]);
	}

	public function getForm()
	{
		return Forms::$plugin->forms->getById($this->form_id);
	}
}