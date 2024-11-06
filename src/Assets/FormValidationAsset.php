<?php 

namespace Ryssbowh\Formidable\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FormValidationAsset extends AssetBundle
{
	public function init(): void
    {
        $this->sourcePath = __DIR__;

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'dist/formValidation.js'
        ];

        $this->css = [
            'src/css/formValidation.css'
        ];

        parent::init();
    }
}