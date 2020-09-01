<?php 

namespace Inspire\Forms\Models\Fields;

use Inspire\Forms\Models\Field;
use Inspire\Forms\Models\FormSubmission;
use craft\elements\Asset;
use craft\web\UploadedFile;
use yii\web\BadRequestHttpException;

class File extends Field
{
	public $label;
	public $required;
	public $enforceRequired;
	public $volume;
	public $extensions;
	public $size;

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
		return 'inspireforms/cp/fields/file';
	}

	/**
	 * inheritDoc
	 **/
	public function getExtraCpRules(): array
	{
		return [
			[['label', 'volume'], 'string'],
			[['label', 'volume'], 'trim'],
			['size', 'number'],
			[['required', 'enforceRequired'], 'boolean'],
			['extensions', 'validateExtensions']
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
				$this->addError('extensions', \Craft::t('inspireforms', 'Invalid extension {ext}', ['ext' => $ext]));
			}
		}
	}

	/**
	 * inheritDoc
	 */
	public function getSiteRules(): array
	{
		$file = [];
		if ($this->size) {
			$file['maxSize'] = 1024*1024*$this->size;
		}
		if ($this->extensions) {
			$file['extensions'] = explode(',', $this->extensions);
		}
		$rules = [
			[$this->name, 'file', $file], 
		];
		if ($this->required) {
			$rules[] = [$this->name, 'required'];
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
		$submission->{$this->name} = UploadedFile::getInstanceByName($this->name);
	}

	/**
	 * Move file to proper volume after validation
	 * 
	 * @param  FormSubmission $submission
	 */
	public function afterSubmissionValidated(FormSubmission $submission)
	{
		$assets = \Craft::$app->getAssets();
        $folder = $assets->getRootFolderByVolumeId($this->volume);
        $file = $submission->{$this->name};
        if (!$folder) {
            throw new BadRequestHttpException(\Craft::t('inspireforms', 'The target folder provided for uploading is not valid'));
        }
        $asset = new Asset();
        $asset->setScenario(Asset::SCENARIO_CREATE);
        $asset->tempFilePath = $file->tempName;
        $asset->filename = $file->name;
        $asset->newFolderId = $folder->id;
        $asset->volumeId = $folder->volumeId;
        $asset->avoidFilenameConflicts = true;
        $result = \Craft::$app->getElements()->saveElement($asset);
        if ($result) {
        	$submission->{$this->name} = $asset->id;
        } else {
        	throw new BadRequestHttpException(\Craft::t('inspireforms', 'Couldn\'t create asset'));
        }
	}

	/**
	 * List craft volumes
	 * 
	 * @return array
	 */
	public function getVolumes(): array
	{
		$volumes = [];
		foreach (\Craft::$app->volumes->getAllVolumes() as $volume) {
			$volumes[$volume->id] = $volume->name;
		}
		return $volumes;
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
		$asset = Asset::find()->where(['assets.id' => $value])->one();
		if ($asset) {
			return '<a target="_blank" href="'.$asset->getCpEditUrl().'">'.$asset->filename.'</a>';
		}
	}

	/**
	 * @inheritDoc
	 */
	public static function getCsvValue($value)
	{
		$asset = Asset::find()->where(['assets.id' => $value])->one();
		if ($asset) {
			return $asset->filename;
		}
	}
}