<?php 

namespace Inspire\Formidable\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SpamAsset extends AssetBundle
{
	public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'spam.js'
        ];

        parent::init();
    }
}
