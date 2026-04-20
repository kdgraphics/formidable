<?php 

namespace Inspire\Formidable\models\Fields;

use Inspire\Formidable\interfaces\FieldTypeInterface;
use Inspire\Formidable\models\Field;
use Inspire\Formidable\models\FieldTypes\ArrayField;
use Inspire\Formidable\models\FieldTypes\FileField;
use Inspire\Formidable\models\FormSubmission;
use craft\elements\Asset;
use craft\web\UploadedFile;
use yii\web\BadRequestHttpException;

class File extends Field
{
	protected $fieldTypeClass = FileField::class;

	public $label;
	public $volume;
	public $extensions;
	public $labelHtml = false;
	public $maxFiles = 1;
	public $attachToUserEmail = false;
	public $attachToAdminEmail = false;
	public $size = 2; //Max file size in Mb
	public $tooltip;
	public $tooltipLabel;

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
	 * inheritDoc
	 **/
	public function fieldType(): FieldTypeInterface
	{
		if ($this->maxFiles != 1) {
			$this->fieldTypeClass = ArrayField::class;
		}
		return $this->fieldTypeClass::getInstance();
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['label', 'volume', 'tooltip', 'tooltipLabel'], 'string'],
			[['label', 'volume', 'tooltip', 'tooltipLabel'], 'trim'],
			['extensions', 'validateExtensions'],
			['size', 'double'],
			['maxFiles', 'integer'],
			[['labelHtml', 'attachToUserEmail', 'attachToAdminEmail'], 'boolean', 'trueValue' => true, 'falseValue' => false],
		];
	}

	/**
	 * Validate extensions
	 * 
	 * @param string $attribute
	 * @param array $params
	 * @param  $validator
	 */
	public function validateExtensions($attribute, $params, $validator)
	{
		$extensions = explode(',', $this->extensions);
		foreach ($extensions as $ext) {
			if (!ctype_alnum($ext)) {
				$this->addError('extensions', \Craft::t('formidable', 'Invalid extension {ext}', ['ext' => $ext]));
			}
		}
	}

	/**
	 * inheritDoc
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		$allowedExtensions = \Craft::$app->getConfig()->getGeneral()->allowedFileExtensions;
		$rules = [
			[$this->handle, 'file', 'extensions' => $allowedExtensions, 'maxFiles' => (int)$this->maxFiles, 'checkExtensionByMimeType' => false], 
		];
		if ($this->extensions) {
			$rules[0]['extensions'] = $this->extensions;
		}
		if ($this->size) {
			$rules[0]['maxSize'] = $this->size * 1024 * 1024;	
		}
		return $rules;
	}

	/**
	 * Upload files before validation
	 * 
	 * @param  FormSubmission $submission
	 */
	public function beforeSubmissionValidated(FormSubmission $submission)
	{
		if ($this->maxFiles != 1) {
			$submission->{$this->handle} = UploadedFile::getInstancesByName($this->frontName);
		} else {
			$submission->{$this->handle} = UploadedFile::getInstanceByName($this->frontName);
		}
	}

	/**
	 * Move file to proper volume after validation
	 * 
	 * @param  FormSubmission $submission
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
		if (!$submission->{$this->handle}) {
			$submission->{$this->handle} = [];
			return;
		} else {
			$files = is_array($submission->{$this->handle}) ? $submission->{$this->handle} : [$submission->{$this->handle}];
		}
		$value = [];
		foreach ($files as $file) {
			$asset = $this->uploadToVolume($file);
			$value[] = $asset->id;
		}
		$submission->{$this->handle} = $value;
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
	public static function getHtmlValue($value): string
	{
        if (!$ids = self::validatedIDs($value)) {
            return '';
        }
		$assets = Asset::find()->id($ids)->all();
		$html = [];
		foreach ($assets as $asset) {
			$html[] = '<a target="_blank" href="'.$asset->getCpEditUrl().'">'.$asset->filename.'</a>';
		}
		return implode('<br/>', $html);
	}

	public static function getAssets($value): array
	{
        if (!$ids = self::validatedIDs($value)) {
            return [];
        }
		return Asset::find()->id($ids)->all();
	}

	/**
	 * @inheritDoc
	 */
	public static function getStringValue($value): string
	{
		if (!$value) {
			return '';
		}
		$assets = self::getAssets($value);
		$string = [];
		foreach ($assets as $asset) {
			$string[] = $asset->filename;
		}
		return implode(', ', $string);
	}

	/**
	 * Upload a file to a volume as an asset
	 * 
	 * @param  UploadedFile $file
	 * @return Asset
	 */
	protected function uploadToVolume(UploadedFile $file): Asset
	{
		$volume = \Craft::$app->volumes->getVolumeByUid($this->volume);
		$assets = \Craft::$app->getAssets();
        $folder = $assets->getRootFolderByVolumeId($volume->id);
        if (!$folder) {
            throw new BadRequestHttpException(\Craft::t('formidable', 'The target folder provided for uploading is not valid'));
        }
        $asset = new Asset();
        $asset->setScenario(Asset::SCENARIO_CREATE);
        $asset->tempFilePath = $file->tempName;
        $asset->filename = $file->name;
        $asset->newFolderId = $folder->id;
        $asset->volumeId = $folder->volumeId;
        $asset->avoidFilenameConflicts = true;
        if (!\Craft::$app->getElements()->saveElement($asset)) {
        	throw new BadRequestHttpException(\Craft::t('formidable', 'Unable to upload the asset to volume ' . $volume->name));
        }
        return $asset;
	}
}
