<?php 

namespace Ryssbowh\Formidable\Assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SubmissionsAsset extends AssetBundle
{
	public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'submissions.js'
        ];

        parent::init();
    }
}