<?php 

namespace Inspire\Formidable\assets;

use craft\web\AssetBundle;

class PhoneInputAsset extends AssetBundle
{
	public function init(): void
    {
        $this->sourcePath = __DIR__;

        $this->js = [
            'PhoneNumber-24.6.0/js/intlTelInput.min.js',
            'dist/utils.js',
            'dist/phoneInput.js'
        ];

        $this->css = [
            'PhoneNumber-24.6.0/css/intlTelInput.min.css',
            'PhoneNumber-24.6.0/css/custom.css'
        ];

        parent::init();
    }
}
