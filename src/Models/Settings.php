<?php

namespace Inspire\Formidable\Models;

use Craft;
use craft\base\Model;
use craft\helpers\Json;
use yii\base\InvalidArgumentException;

class Settings extends Model
{
    public $siteKey = '';
    public $secretKey = '';
    public $acceptableScore = 5;
    public $deleteSubmissionsThreshold = '';
    public $deleteSpamThreshold = 30;
    public $useQueue = false;
    public $spamFrequency = 10;
    public $honeypotCss = '{"position": "absolute", "left": "-100000px", "overflow": "hidden"}';

    /**
     * @return array
     */
    public function defineRules(): array
    {
        return [
            [['siteKey', 'secretKey', 'deleteSpamThreshold', 'deleteSubmissionsThreshold', 'honeypotCss'], 'string'],
            [['acceptableScore', 'spamFrequency'], 'number'],
            [['acceptableScore', 'honeypotCss'], 'required'],
            [['useQueue'], 'boolean'],
            ['honeypotCss', function () {
                try {
                    Json::decode($this->honeypotCss);
                } catch (InvalidArgumentException $e) {
                    $this->addError('honeypotCss', \Craft::t('formidable', 'This is not a valid JSON string'));
                }
            }, 'skipOnEmpty' => false]
        ];
    }
}
