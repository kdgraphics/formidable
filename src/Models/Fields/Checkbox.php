<?php 

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FieldTypes\BooleanField;
use Inspire\Formidable\Models\FormPosted;

class Checkbox extends Field
{
    protected $fieldTypeClass = BooleanField::class;

    public $label;
    public $default;
    public $labelHtml = false;
    public $tooltip;
    public $tooltipLabel;

    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'checkbox';
    }

    /**
     * inheritDoc
     **/
    public function getValue()
    {
        return $this->_value ?? $this->default;
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'Checkbox';
    }

    /**
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return ['label', 'labelHtml', 'tooltip', 'tooltipLabel', 'default'];
    }

    /**
     * inheritDoc
     **/
    public function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['label', 'tooltip', 'tooltipLabel'], 'string'],
            [['label', 'tooltip', 'tooltipLabel'], 'trim'],
            [['default', 'labelHtml'], 'boolean', 'trueValue' => true, 'falseValue' => false]
        ]);
    }

    /**
     * Upload files before validation
     * 
     * @param  FormPosted $posted
     */
    public function beforeSubmissionValidated(FormPosted $posted)
    {
        $posted->{$this->handle} = $posted->{$this->handle} ? true : false;
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormPosted $posted): array
    {
        return [[$this->handle, 'boolean']];
    }

    /**
     * @inheritDoc
     */
    public static function getStringValue(?FieldInterface $field, $value, bool $isOwn = true): string
    {
        return $value ? \Craft::t('site', 'Yes') : \Craft::t('site', 'No');
    }
}