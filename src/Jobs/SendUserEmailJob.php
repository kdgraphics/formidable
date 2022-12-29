<?php 

namespace Inspire\Formidable\Jobs;

use Inspire\Formidable\Formidable;
use craft\queue\BaseJob;

class SendUserEmailJob extends BaseJob
{
    /**
     * @var int
     */
    public $submissionId;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $submission = Formidable::$plugin->submissions->getById($this->submissionId);
        Formidable::$plugin->submissions->sendUserEmail($submission);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return \Craft::t('formidable', 'Sending user form submission email');
    }
}