<?php 

namespace Ryssbowh\Formidable\Models\Fields;

use Ryssbowh\Formidable\Interfaces\FieldTypeInterface;
use Ryssbowh\Formidable\Models\Field;
use Ryssbowh\Formidable\Models\FieldTypes\ArrayField;
use Ryssbowh\Formidable\Models\FieldTypes\FileField;
use Ryssbowh\Formidable\Models\FormSubmission;
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
	public $multiple = false;
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
	public function backendTemplate(): string
	{
		return 'formidable/cp/fields/file';
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
			[['labelHtml', 'multiple'], 'boolean', 'trueValue' => true, 'falseValue' => false],
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
	 **/
	public function fieldType(): FieldTypeInterface
	{
		if ($this->multiple) {
				$this->fieldTypeClass = ArrayField::class;
		}
		return $this->fieldTypeClass::getInstance();
	}

	/**
	 * inheritDoc
	 */
	public function getFrontEndRules(FormSubmission $submission): array
	{
		if ($this->multiple) {
			$rules = [
				[$this->handle, 'each', 'rule' => ['file']], 
			];
			if ($this->extensions) {
				$rules[0]['rule']['extensions'] = explode(',', $this->extensions);
			}
			return $rules;	
		}
		$rules = [
			[$this->handle, 'file'], 
		];
		if ($this->extensions) {
			$rules[0]['extensions'] = explode(',', $this->extensions);
		}
		return $rules;
	}

	/**
	 * Upload file before validation
	 * 
	 * @param  FormSubmission $submission
	 */
	public function beforeSubmissionValidated(FormSubmission $submission)
	{
		$submission->{$this->handle} = UploadedFile::getInstancesByName($this->handle);
	}

	/**
	 * Move file to proper volume after validation
	 * 
	 * @param  FormSubmission $submission
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
		$files = $submission->{$this->handle};
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
	public static function getSubmissionValue($value)
	{
		if (!$value) {
			return '';
		}
		if (is_string($value)) {
			$value = json_decode($value, true);
		}
		$assets = Asset::find()->id($value)->all();
		$html = '';
		foreach ($assets as $asset) {
			$html .= '<a target="_blank" href="'.$asset->getCpEditUrl().'">'.$asset->filename.'</a><br/>';
		}
		return $html;
	}

	/**
	 * @inheritDoc
	 */
	public static function getCsvValue($value)
	{
		if (!$value) {
			return '';
		}
		if (is_string($value)) {
			$value = json_decode($value, true);
		}
		$assets = Asset::find()->id($value)->all();
		$csv = [];
		foreach ($assets as $asset) {
			$csv[] = $asset->filename;
		}
		return implode(',', $csv);
	}

	/**
	 * Upload a file to a volume as an asset
	 * 
	 * @param  UploadedFile $file
	 * @return Asset
	 */
	protected function uploadToVolume(UploadedFile $file): Asset
	{
		$assets = \Craft::$app->getAssets();
        $folder = $assets->getRootFolderByVolumeId($this->volume);
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