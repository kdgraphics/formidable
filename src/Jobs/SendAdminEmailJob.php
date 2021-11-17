<?php 

namespace Ryssbowh\Formidable\Jobs;

use Ryssbowh\Formidable\Formidable;
use craft\mail\Message;
use craft\queue\BaseJob;

class SendAdminEmailJob extends BaseJob
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
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $email = \Craft::$app->getMailer()
            ->composeFromKey($this->form->notifyAdmin_key, [
                'submission' => $this->submission,
                'form' => $this->form
            ])->setTo(explode(',', $this->form->notifyEmails));
        $this->attachFiles($email);

        $event = Formidable::$plugin->submissions->sendBeforeAdminEmailEvent($email, $this->form, $this->submission);
        if (!$event->isValid) {
            return;
        }

        if (!$email->send()) {
            throw new \Exception('Unable to send form submission email to ' . $this->form->notifyEmails);
        } else {
            if ($this->submission->id) {
                $record = Formidable::$plugin->submissions->getRecordById($this->submission->id);
                $record->adminNotified = 1;
                $record->save(false);
            }
        }
    }

    protected function attachFiles(Message $email)
    {
        $fileFields = $this->form->getFieldsByType('file');
        $assets = [];
        foreach ($fileFields as $field) {
            if (!$field->attachToAdminEmail) {
                continue;
            }
            $assets = array_merge($assets, $field::getAssets($this->submission->getValue($field->handle)));
        }
        foreach ($assets as $asset) {
            $path = \Craft::getAlias($asset->volume->path . '/' . $asset->getPath($asset->getFilename(true)));
            $email->attach($path);
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