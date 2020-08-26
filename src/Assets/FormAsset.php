<?php 

namespace Inspire\Forms\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FormAsset extends AssetBundle
{
	public function init()
    {
        $this->sourcePath = "@Inspire/Forms/Assets/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'form.js'
        ];

        parent::init();
    }
}