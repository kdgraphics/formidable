<?php 

namespace Ryssbowh\Formidable\Jobs;

use Ryssbowh\Formidable\Formidable;
use craft\queue\BaseJob;

class SendUserEmailJob extends BaseJob
{
    /**
     * @var Form
     */
    public $form;

    /**
     * @var Submission
     */
    public $submission;

    /**
     * @var array
     */
    public $formSubmission;

    /**
     * @var string
     */
    public $email;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $email = \Craft::$app->getMailer()
            ->composeFromKey($this->form->notify_key, [
                'submission' => $this->submission,
                'formSubmission' => $this->formSubmission,
                'form' => $this->form
            ])->setTo($this->email);

        if (!$email->send()) {
            throw new \Exception('Unable to send form submission email to ' . $this->email);
        } else {
            if ($this->submission) {
                $record = Formidable::$plugin->submissions->getRecordById($this->submission->id);
                $record->notified = 1;
                $record->save(false);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return \Craft::t('formidable', 'Sending user form submission email');
    }
}