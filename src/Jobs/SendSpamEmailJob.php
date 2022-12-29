<?php 

namespace Inspire\Formidable\Jobs;

use Inspire\Formidable\Formidable;
use craft\queue\BaseJob;

class SendSpamEmailJob extends BaseJob
{
    /**
     * @var int
     */
    public $spamId;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $spam = Formidable::$plugin->spam->getById($this->spamId);
        Formidable::$plugin->spam->sendEmail($spam);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return \Craft::t('formidable', 'Sending form spam email');
    }
}