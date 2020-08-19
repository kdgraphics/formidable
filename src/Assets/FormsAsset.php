<?php 

namespace Inspire\Forms\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FormsAsset extends AssetBundle
{
	public function init()
    {
        $this->sourcePath = "@Inspire/Forms/Assets/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'forms.js'
        ];

        parent::init();
    }
}