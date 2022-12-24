<?php 

namespace Inspire\Formidable\Jobs;

use Inspire\Formidable\Formidable;
use craft\queue\BaseJob;

class SendAdminEmailJob extends BaseJob
{
    /**
     * @var Submission
     */
    public $submission;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        Formidable::$plugin->submissions->sendAdminEmail($this->submission);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return \Craft::t('formidable', 'Sending admin form submission email');
    }
}