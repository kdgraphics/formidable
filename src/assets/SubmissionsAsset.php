<?php 

namespace Inspire\Formidable\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SubmissionsAsset extends AssetBundle
{
	public function init(): void
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
