<?php 

namespace Ryssbowh\Formidable\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PhoneInputAsset extends AssetBundle
{
	public function init(): void
    {
        $this->sourcePath = __DIR__;

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'PhoneNumber-24.6.0/js/intlTelInput.min.js',
            'dist/utils.js',
            'dist/phoneInput.js'
        ];

        $this->css = [
            'PhoneNumber-24.6.0/css/intlTelInput.min.css'
        ];

        parent::init();
    }
}