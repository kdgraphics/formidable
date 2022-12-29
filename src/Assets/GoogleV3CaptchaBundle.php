<?php 

namespace Inspire\Formidable\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\redactor\assets\redactor\RedactorAsset;

class GoogleV3CaptchaBundle extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/src';

        $this->js = [
            'captchaV3.js',
        ];

        parent::init();
    }
}