<?php

namespace Inspire\Formidable\services;

use Craft;
use Inspire\Formidable\exceptions\FormEmailException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\jobs\SendAdminEmailJob;
use Inspire\Formidable\jobs\SendUserEmailJob;
use Inspire\Formidable\models\Form;
use Inspire\Formidable\models\Submission as SubmissionModel;
use craft\helpers\Queue;
use craft\mail\Message;

class SubmissionMailService
{
    public function __construct(private SubmissionsService $submissionsService)
    {
    }

    public function sendUserEmail(SubmissionModel $submission): bool
    {
        $recipient = $submission->getValue($submission->form->notifyField);
        $email = Craft::$app->getMailer()
            ->composeFromKey($submission->form->notify_key, [
                'submission' => $submission,
                'form' => $submission->form,
            ])
            ->setTo($recipient);

        $this->attachSubmissionFiles($email, $submission, 'user');
        $event = $this->submissionsService->sendBeforeUserEmailEvent($email, $submission);
        if (!$event->isValid) {
            return false;
        }
        if ($email->send()) {
            if ($submission->id) {
                $record = $this->submissionsService->getRecordById($submission->id);
                $record->notified = 1;
                $record->save(false);
            }

            return true;
        }

        throw new FormEmailException('Unable to send user email');
    }

    public function sendAdminEmail(SubmissionModel $submission): bool
    {
        $emails = [];
        foreach (explode(',', trim($submission->form->notifyEmails)) as $address) {
            $address = trim($address);
            $address = Craft::parseEnv($address);
            if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $address;
            }
        }

        $email = Craft::$app->getMailer()
            ->composeFromKey($submission->form->notifyAdmin_key, [
                'submission' => $submission,
                'form' => $submission->form,
            ])
            ->setTo($emails);

        $this->attachSubmissionFiles($email, $submission, 'admin');
        $event = $this->submissionsService->sendBeforeAdminEmailEvent($email, $submission);
        if (!$event->isValid) {
            return false;
        }
        if ($email->send()) {
            if ($submission->id) {
                $record = $this->submissionsService->getRecordById($submission->id);
                $record->adminNotified = 1;
                $record->save(false);
            }

            return true;
        }

        throw new FormEmailException('Unable to send admin email');
    }

    public function queueOrSendUserEmail(Form $form, SubmissionModel $submission): bool
    {
        if (!$form->notify_key) {
            return false;
        }
        if (!filter_var($submission->getValue($form->notifyField), FILTER_VALIDATE_EMAIL)) {
            Formidable::log("Can't notify user for form {$form->handle} : Value {$submission->getValue($form->notifyField)} for field {$form->notifyField} is not a valid email");

            return false;
        }
        if (Formidable::$plugin->settings->useQueue) {
            Queue::push(new SendUserEmailJob(['submission' => $submission]));

            return true;
        }

        return $this->sendUserEmail($submission);
    }

    public function queueOrSendAdminEmail(Form $form, SubmissionModel $submission): bool
    {
        if (!$form->notifyAdmin_key) {
            return false;
        }
        if (Formidable::$plugin->settings->useQueue) {
            Queue::push(new SendAdminEmailJob(['submission' => $submission]));

            return true;
        }

        return $this->sendAdminEmail($submission);
    }

    private function attachSubmissionFiles(Message $email, SubmissionModel $submission, string $type): void
    {
        $fileFields = $submission->form->getFieldsByType('file');
        $assets = [];

        foreach ($fileFields as $field) {
            if ($type === 'user' && !$field->attachToUserEmail) {
                continue;
            }
            if ($type === 'admin' && !$field->attachToAdminEmail) {
                continue;
            }
            $assets = array_merge($assets, $field::getAssets($submission->getValue($field->handle)));
        }

        foreach ($assets as $asset) {
            $path = Craft::getAlias($asset->fs->path . '/' . $asset->getPath($asset->getFilename(true)));
            $email->attach($path);
        }
    }
}
