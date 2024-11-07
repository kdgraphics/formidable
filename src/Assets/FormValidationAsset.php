<?php 

namespace Ryssbowh\Formidable\Assets;

use craft\web\AssetBundle;

class FormValidationAsset extends AssetBundle
{
	public function init(): void
    {
        $this->sourcePath = __DIR__;

        $this->js = [
            'dist/formValidation.js'
        ];

        $this->css = [
            'src/css/formValidation.css'
        ];

        parent::init();
    }
}