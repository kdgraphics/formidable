<?php 

namespace Ryssbowh\Formidable\Jobs;

use Ryssbowh\Formidable\Formidable;
use craft\queue\BaseJob;

class SendSpamEmailJob extends BaseJob
{
    /**
     * @var Form
     */
    public $form;

    /**
     * @var ?Spam
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
        $email = \Craft::$app->getMailer()
            ->composeFromKey($this->form->notifySpam_key, [
                'spam' => $this->spam,
                'formSubmission' => $this->formSubmission,
                'form' => $this->form
            ])->setTo(explode(',', $this->form->notifySpamEmails));

        if (!$email->send()) {
            throw new \Exception('Unable to send spam email to ' . $this->form->notifySpamEmails);
        } else {
            if ($this->spam) {
                $record = Formidable::$plugin->spam->getRecordById($this->spam->id);
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
        return \Craft::t('formidable', 'Sending a spam email');
    }
}