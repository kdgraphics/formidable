<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FieldTypes\BooleanField;
use Ryssbowh\Formidable\Models\FormSubmission;

class LightSwitch extends Field
{
	protected $fieldTypeClass = BooleanField::class;

	public $label;
	public $default;

	/**
	 * inheritDoc
	 **/
	public function handle(): string
	{
		return 'switch';
	}

	/**
	 * inheritDoc
	 **/
	public function friendlyName(): string
	{
		return 'Switch';
	}

	/**
	 * inheritDoc
	 **/
	public function backendTemplate(): string
	{
		return 'formidable/cp/fields/switch';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			['label', 'string'],
			['label', 'trim'],
			[['default'], 'boolean']
		];
	}

	/**
	 * inheritDoc
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		return [[$this->handle, 'boolean']];
	}

	/**
	 * @inheritDoc
	 */
	public static function getFriendlyValue($value)
	{
		return $value ? \Craft::t('site', 'Yes') : \Craft::t('site', 'No');
	}
}