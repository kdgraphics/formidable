<?php
namespace Inspire\Forms;

use Craft;
use craft\base\Plugin;

class Forms extends Plugin
{
    public $hasCpSettings = true;

    public $controllerNamespace = "Inspire\\Forms\\controllers";

    public $schemaVersion = '1.0.0';

    public function init()
    {
        parent::init();
    }
}
