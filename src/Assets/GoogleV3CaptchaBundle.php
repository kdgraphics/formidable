<?php

namespace Inspire\Formidable\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class GoogleV3CaptchaBundle extends AssetBundle
{
	public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->js = [
            'captcha.js',
        ];

        parent::init();
    }
}
