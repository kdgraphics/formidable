<?php 

namespace Ryssbowh\Formidable\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\redactor\assets\redactor\RedactorAsset;

class FormsAsset extends AssetBundle
{
	public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CpAsset::class,
            RedactorAsset::class
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