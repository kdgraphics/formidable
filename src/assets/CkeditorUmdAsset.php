<?php

namespace Inspire\Formidable\assets;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class CkeditorUmdAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = Craft::getAlias('@vendor/craftcms/ckeditor/src/web/assets/ckeditor/dist');

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'lib/ckeditor5.umd.js',
        ];

        $this->css = [
            'lib/ckeditor5.css',
            'ckeditor.css',
        ];

        parent::init();
    }
}
