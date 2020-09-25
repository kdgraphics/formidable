<?php

namespace Ryssbowh\Formidable\Models;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public $siteKey = '';
    public $secretKey = '';
    public $acceptableScore = 5;
    public $sendInSchedule = false;
    public $forceUnlock = 20;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['siteKey', 'string'],
            ['secretKey', 'string'],
            ['sendInSchedule', 'boolean'],
            [['acceptableScore', 'forceUnlock'], 'number'],
            [['acceptableScore', 'forceUnlock'], 'required']
        ];
    }
}
