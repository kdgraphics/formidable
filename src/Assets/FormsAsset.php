<?php 

namespace Inspire\Formidable\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\redactor\assets\redactor\RedactorAsset;

class FormsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . "/dist/js";

        $this->depends = [
            CpAsset::class,
            RedactorAsset::class
        ];

        $this->js = [
            'chunk-vendors.js',
            'chunk-common.js',
            'forms.js'
        ];

        parent::init();
    }
}