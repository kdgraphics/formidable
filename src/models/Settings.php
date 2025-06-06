<?php

namespace Inspire\Formidable\models;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public $siteKey = '';
    public $secretKey = '';
    public $acceptableScore = 5;
    public $deleteSubmissionsThreshold = '';
    public $deleteSpamThreshold = 30;
    public $useQueue = false;

    /**
     * @return array
     */
    public function defineRules(): array
    {
        return [
            [['siteKey', 'secretKey', 'deleteSpamThreshold', 'deleteSubmissionsThreshold'], 'string'],
            [['acceptableScore'], 'number'],
            [['acceptableScore'], 'required'],
            [['useQueue'], 'boolean']
        ];
    }
}
