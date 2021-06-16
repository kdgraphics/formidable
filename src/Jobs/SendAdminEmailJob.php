<?php 

namespace Ryssbowh\Formidable\Jobs;

use Ryssbowh\Formidable\Formidable;
use craft\queue\BaseJob;

class SendAdminEmailJob extends BaseJob
{
    /**
     * @var Form
     */
    public $form;

    /**
     * @var ?Submission
     */
    public $submission;

    /**
     * @var array
     */
    public $formSubmission;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $email = \Craft::$app->getMailer()
            ->composeFromKey($this->form->notifyAdmin_key, [
                'submission' => $this->submission,
                'formSubmission' => $this->formSubmission,
                'form' => $this->form
            ])->setTo(explode(',', $this->form->notifyEmails));

        if (!$email->send()) {
            throw new \Exception('Unable to send form submission email to ' . $this->form->notifyEmails);
        } else {
            if ($this->submission) {
                $record = Formidable::$plugin->submissions->getRecordById($this->submission->id);
                $record->adminNotified = 1;
                $record->save(false);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return \Craft::t('formidable', 'Sending admin form submission email');
    }
}