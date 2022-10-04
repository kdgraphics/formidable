<?php 

namespace Ryssbowh\Formidable\Jobs;

use Ryssbowh\Formidable\Formidable;
use craft\queue\BaseJob;

class SendUserEmailJob extends BaseJob
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
        Formidable::$plugin->submissions->sendUserEmail($this->submission);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return \Craft::t('formidable', 'Sending user form submission email');
    }
}