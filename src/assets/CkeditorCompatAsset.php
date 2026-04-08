<?php

namespace Inspire\Formidable\assets;

use craft\web\AssetBundle;

class CkeditorCompatAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CkeditorUmdAsset::class,
        ];

        $this->js = [
            'default-ckeditor-configs.js',
            'ckeditor-compat.js',
        ];

        parent::init();
    }
}
