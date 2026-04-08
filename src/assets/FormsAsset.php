<?php

namespace Inspire\Formidable\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FormsAsset extends AssetBundle
{
	public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CpAsset::class,
            CkeditorCompatAsset::class,
            HookAsset::class,
        ];

        $this->js = [
            'forms.js',
        ];

        $this->css = [
            'main.css'
        ];

        parent::init();
    }
}
