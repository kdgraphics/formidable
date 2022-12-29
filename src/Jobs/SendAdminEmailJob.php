<?php 

namespace Inspire\Formidable\Jobs;

use Inspire\Formidable\Formidable;
use craft\queue\BaseJob;

class SendAdminEmailJob extends BaseJob
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
        Formidable::$plugin->submissions->sendAdminEmail($submission);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return \Craft::t('formidable', 'Sending admin form submission email');
    }
}