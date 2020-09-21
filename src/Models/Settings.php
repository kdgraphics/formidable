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

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['siteKey', 'string'],
            ['secretKey', 'string'],
            ['sendInSchedule', 'boolean'],
            ['acceptableScore', 'number'],
            [['acceptableScore'], 'required']
        ];
    }
}
