<?php 

namespace Ryssbowh\Formidable\Jobs;

use Ryssbowh\Formidable\Formidable;
use craft\mail\Message;
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
                'form' => $this->form
            ])->setTo($this->email);
        $this->attachFiles($email);

        $event = Formidable::$plugin->submissions->sendBeforeUserEmailEvent($email, $this->form, $this->submission);
        if (!$event->isValid) {
            return;
        }

        if (!$email->send()) {
            throw new \Exception('Unable to send form submission email to ' . $this->email);
        } else {
            if ($this->submission->id) {
                $record = Formidable::$plugin->submissions->getRecordById($this->submission->id);
                $record->notified = 1;
                $record->save(false);
            }
        }
    }

    protected function attachFiles(Message $email)
    {
        $fileFields = $this->form->getFieldsByType('file');
        $assets = [];
        foreach ($fileFields as $field) {
            if (!$field->attachToUserEmail) {
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
        return \Craft::t('formidable', 'Sending user form submission email');
    }
}