<?php

namespace Inspire\Formidable\Models\Fields;

use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Interfaces\FieldTypeInterface;
use Inspire\Formidable\Models\Field;
use Inspire\Formidable\Models\FieldTypes\FileField;
use Inspire\Formidable\Models\FormPosted;
use craft\elements\Asset;
use craft\web\UploadedFile;
use yii\web\BadRequestHttpException;

class File extends Field
{
    protected $fieldTypeClass = FileField::class;

    public $label;
    public $volume;
    public $folder;
    public $accepts;
    public $labelHtml = false;
    public $attachToUserEmail = false;
    public $attachToAdminEmail = false;
    public $tooltip;
    public $tooltipLabel;
    public $multiple = false;

    /**
     * inheritDoc
     **/
    public function handle(): string
    {
        return 'file';
    }

    /**
     * inheritDoc
     **/
    public function friendlyName(): string
    {
        return 'File';
    }

    /**
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return ['label', 'labelHtml', 'multiple', 'tooltip', 'tooltipLabel', 'volume', 'folder', 'accepts', 'attachToAdminEmail', 'attachToUserEmail'];
    }

    /**
     * inheritDoc
     **/
    public function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['label', 'volume', 'tooltip', 'tooltipLabel', 'folder', 'accepts'], 'string'],
            [['label', 'volume', 'tooltip', 'tooltipLabel', 'folder'], 'trim'],
            ['volume', 'required'],
            [['labelHtml', 'attachToUserEmail', 'attachToAdminEmail', 'multiple'], 'boolean', 'trueValue' => true, 'falseValue' => false],
        ]);
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormPosted $posted): array
    {
        $allowedExtensions = \Craft::$app->getConfig()->getGeneral()->allowedFileExtensions;
        $rules = [
            [$this->handle, 'file', 'extensions' => $allowedExtensions, 'checkExtensionByMimeType' => false, 'maxFiles' => $this->multiple ? 0 : 1], 
        ];
        return $rules;
    }

    /**
     * Upload files before validation
     * 
     * @param  FormPosted $posted
     */
    public function beforeSubmissionValidated(FormPosted $posted)
    {
        $posted->{$this->handle} = array_merge($posted->{$this->handle} ?? [], UploadedFile::getInstancesByName($this->frontName));
    }

    /**
     * Move file to proper volume after validation
     * 
     * @param  FormPosted $submission
     */
    public function afterSubmissionValidated(FormPosted $posted)
    {
        if (!$posted->{$this->handle}) {
            $posted->{$this->handle} = [];
            return;
        }
        $value = [];
        foreach ($posted->{$this->handle} as $file) {
            if ($file instanceof UploadedFile) {
                $asset = Formidable::$plugin->assets->uploadToVolume($file, $this->volume, $this->folder);
                $value[] = $asset->id;
            } else {
                $value[] = $file;
            }
        }
        $posted->{$this->handle} = $value;
    }

    /**
     * Adds a dot on every extension
     * 
     * @return string
     */
    public function getHtmlExtensions(): string
    {
        return implode(',', array_map(function($ext){
            return '.'.$ext;
        }, explode(',', $this->extensions)));
    }

    /**
     * @inheritDoc
     */
    public static function getHtmlValue(?FieldInterface $field, $value, bool $isOwn = true): string
    {
        if (!$value) {
            return '';
        }
        $assets = Asset::find()->id($value)->all();
        $html = [];
        foreach ($assets as $asset) {
            $html[] = '<a target="_blank" href="'.$asset->getCpEditUrl().'">'.$asset->filename.'</a>';
        }
        return implode('<br/>', $html);
    }

    /**
     * @inheritDoc
     */
    public static function getStringValue(?FieldInterface $field, $value, bool $isOwn = true): string
    {
        if (!$value) {
            return '';
        }
        $string = [];
        foreach ($value as $file) {
            if ($file instanceof UploadedFile) {
                $string[] = $file->name;
            } else {
                $asset = Asset::find()->id($file)->one();
                $string[] = $asset->filename;
            }
        }
        return implode(', ', $string);
    }
}