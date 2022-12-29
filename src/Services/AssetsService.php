<?php 

namespace Inspire\Formidable\Services;

use craft\base\Component;
use craft\elements\Asset;
use craft\web\UploadedFile;
use yii\web\BadRequestHttpException;

class AssetsService extends Component
{
	/**
     * Upload a file to a volume as an asset
     * 
     * @param  UploadedFile $file
     * @param  string       $volumeUid
     * @param  ?string      $folder
     * @return Asset
     */
    public function uploadToVolume(UploadedFile $file, string $volumeUid, ?string $folder): Asset
    {
        $volume = \Craft::$app->volumes->getVolumeByUid($volumeUid);
        if (!$volume) {
            throw new BadRequestHttpException(\Craft::t('formidable', 'Unable to upload the asset, volume not found : ' . $volumeUid));
        }
        $assets = \Craft::$app->getAssets();
        if (!$folder) {
            $folder = $assets->getRootFolderByVolumeId($volume->id);
        } else {
            $folder = $assets->ensureFolderByFullPathAndVolume($folder, $volume);
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