<?php 

namespace Ryssbowh\Formidable\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ValidationAsset extends AssetBundle
{
	public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->js = [
            'validation.js'
        ];

        parent::init();
    }
}