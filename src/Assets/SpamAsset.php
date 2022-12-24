<?php 

namespace Inspire\Formidable\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SpamAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist/js';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'chunk-vendors.js',
            'chunk-common.js',
            'spam.js'
        ];

        parent::init();
    }
}