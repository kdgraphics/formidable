<?php 

namespace Inspire\Forms\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SpamAsset extends AssetBundle
{
	public function init()
    {
        $this->sourcePath = "@Inspire/Forms/Assets/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'spam.js'
        ];

        parent::init();
    }
}