<?php 

namespace Inspire\Formidable\Jobs;

use Inspire\Formidable\Formidable;
use craft\queue\BaseJob;

class SendSpamEmailJob extends BaseJob
{
    /**
     * @var Spam
     */
    public $spam;

    /**
     * @var array
     */
    public $formSubmission;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        Formidable::$plugin->spam->sendEmail($this->spam, $this->formSubmission);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return \Craft::t('formidable', 'Sending form spam email');
    }
}