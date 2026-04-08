<?php

namespace Inspire\Formidable\services;

use Craft;
use Inspire\Formidable\events\EmailEvent;
use Inspire\Formidable\exceptions\FormEmailException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\jobs\SendAdminEmailJob;
use Inspire\Formidable\jobs\SendUserEmailJob;
use Inspire\Formidable\models\Form;
use Inspire\Formidable\models\FormSubmission;
use Inspire\Formidable\models\Records\Form as FormRecord;
use Inspire\Formidable\models\Records\Submission;
use Inspire\Formidable\models\Records\SubmissionValue;
use Inspire\Formidable\models\Submission as SubmissionModel;
use Inspire\Formidable\models\SubmissionValue as SubmissionValueModel;
use Inspire\Formidable\providers\SubmissionProvider;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\mail\Message;
use yii\base\Event;
use yii\data\ActiveDataProvider;

class SubmissionsService extends Component
{
    public const EVENT_BEFORE_SEND_USER_EMAIL = 'before-send-user-email';
    public const EVENT_BEFORE_SEND_ADMIN_EMAIL = 'before-send-admin-email';

    public ?int $currentSiteId = null;
    private ?SubmissionQueryService $queryService = null;
    private ?SubmissionMailService $mailService = null;

    /**
     * @return array
     */
    public function getSites(): array
    {
        $return = [];

        $groups = Craft::$app->getSites()->getAllGroups();
        foreach ($groups as $group) {
            $sites = Craft::$app->getSites()->getEditableSitesByGroupId($group->id);
            foreach ($sites as $site) {
                $return[$group->getName()][$site->id] = $site->name;
            }
        }

        return $return;
    }

    /**
     * Creates a submission from a form submission
     *
     * @param  Form           $form
     * @param  FormSubmission $formSubmission
     * @return ?SubmissionModel
     */
    public function create(Form $form, FormSubmission $formSubmission): ?SubmissionModel
    {
        $submission = new SubmissionModel([
            'form_id' => $form->id,
            'siteId' => Craft::$app->getSites()->getCurrentSite()->id,
        ]);
        $values = [];
        foreach ($form->fields as $field) {
            $values[] = new SubmissionValueModel([
                'field_id' => $field->id,
                'value' => json_encode($formSubmission->{$field->handle}),
                'type' => $field->handle()
            ]);
        }
        $submission->values = $values;
        if ($form->saveSubmissions) {
            $this->saveSubmission($submission);
        }
        $this->queueUserEmail($form, $submission);
        $this->queueAdminEmail($form, $submission);
        return $submission;
    }

    /**
     * Get submission model by id
     *
     * @param  int $id
     * @return ?SubmissionModel
     */
    public function getById(int $id): ?SubmissionModel
    {
        $record = Submission::find()->where(['id' => $id])->one();
        return $record ? $record->toModel() : null;
    }

    /**
     * Get all values for a submission
     *
     * @param  SubmissionModel $submission
     * @return array
     */
    public function getValuesForSubmission(SubmissionModel $submission): array
    {
        $this->currentSiteId = $submission->siteId;

        return array_map(function ($sub) {
            return $sub->toModel();
        }, SubmissionValue::find()->where(['submission_id' => $submission->id])->all());
    }

    /**
     * Saves a submission
     *
     * @param  SubmissionModel $submission
     */
    public function saveSubmission(SubmissionModel $submission)
    {
        $record = $submission->id ? $this->getRecordById($submission->id) : new Submission();
        foreach ($submission->attributes as $name => $value) {
            // there is no db field for siteName
            if ($name === 'siteName') {
                continue;
            }

            $record->$name = $value;
        }
        $record->save(false);
        $submission->setAttributes($record->attributes);
        foreach ($submission->values as $value) {
            $record = $value->id ? $this->getValueRecordById($value->id) : new SubmissionValue();
            foreach ($value->attributes as $name => $value2) {
                $record->$name = $value2;
            }
            $record->submission_id = $submission->id;
            $record->save(false);
            $value->setAttributes($record->attributes);
        }
    }

    /**
     * Get submission record by id
     *
     * @param  int $id
     * @return Submission
     */
    public function getRecordById(int $id): Submission
    {
        return Submission::find()->where(['id' => $id])->one() ?? new Submission();
    }

    /**
     * Get submission value record by id
     *
     * @param  int $id
     * @return SubmissionValue
     */
    public function getValueRecordById(int $id): SubmissionValue
    {
        return SubmissionValue::find()->where(['id' => $id])->one() ?? new SubmissionValue();
    }

    /**
     * Counts the amount os unread submissions
     *
     * @return int
     */
    public function countUnread(): int
    {
        return Submission::find()
            ->where(['read' => 0])
            ->count();
    }

    /**
     * Delete a submission by id
     *
     * @param  int    $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        if ($submission = Submission::find()->where(['id' => $id])->one()) {
            if (empty($submission->dateDeleted)) {
                return $submission->softDelete();
            } else {
                return $submission->delete();
            }
        }
        return false;
    }

    /**
     * Run garbage collection
     */
    public function runGc()
    {
        $this->getQueryService()->runGc();
    }

    /**
     * Delete many submissions bt ids
     *
     * @param  array  $ids
     */
    public function deleteMany(array $ids)
    {
        $_this = $this;
        array_map(function ($id) use ($_this) {
            $_this->delete($id);
        }, $ids);
    }

    /**
     * Set many submissions as read
     *
     * @param array $ids
     * @return bool
     */
    public function setManyAsRead(array $ids): bool
    {
        $submissions = Submission::find()->where(['id' => $ids])->all();
        foreach ($submissions as $sub) {
            $sub->read = 1;
            $sub->save(false);
        }
        return true;
    }

    /**
     * Export submissions ids as csv
     * @param  array  $ids
     * @return ?string
     */
    public function exportIdsAsCsv(array $ids): ?string
    {
        $query = Submission::find()->where(['in', 'formidable_submissions.id', $ids]);
        return Formidable::$plugin->csvExporter->exportFromQuery($query);
    }

    /**
     * Export submissions as csv according to some filters
     * @param  array  $filters
     * @return ?string
     */
    public function exportAllAsCsv(array $filters, array $sort): ?string
    {
        return $this->getQueryService()->exportAllAsCsv($filters, $sort);
    }

    /**
     * Get some submissions, filtered and pagered
     *
     * @param  array   $filters
     * @param  array   $sort
     * @param  integer $page
     * @param  integer $pageSize
     * @return ActiveDataProvider
     */
    public function get(array $filters = [], ?array $sort = null, $page = 0, $pageSize = 10): ActiveDataProvider
    {
        return $this->getQueryService()->get($filters, $sort, (int) $page, (int) $pageSize);
    }

    /**
     * Get some submissions, filtered
     *
     * @param  array   $filters
     * @return ActiveDataProvider
     */
    public function getAll(array $filters = []): ActiveDataProvider
    {
        return $this->getQueryService()->getAll($filters);
    }

    /**
     * Build data for a chart js instance
     *
     * @param  array  $models
     * @return array
     */
    public function normalizeDataForChart(array $models): array
    {
        return $this->getQueryService()->normalizeDataForChart($models);
    }

    /**
     * Trigger email event to allow other plugin to modify the email
     *
     * @param  Message         $email
     * @param  SubmissionModel $submission
     * @return EmailEvent
     */
    public function sendBeforeUserEmailEvent(Message $email, SubmissionModel $submission): EmailEvent
    {
        $event = new EmailEvent([
            'email' => $email,
            'form' => $submission->form,
            'submission' => $submission
        ]);
        $this->trigger(self::EVENT_BEFORE_SEND_USER_EMAIL, $event);
        return $event;
    }

    /**
     * Trigger email event to allow other plugin to modify the email
     *
     * @param  Message         $email
     * @param  SubmissionModel $submission
     * @return EmailEvent
     */
    public function sendBeforeAdminEmailEvent(Message $email, SubmissionModel $submission): EmailEvent
    {
        $event = new EmailEvent([
            'email' => $email,
            'form' => $submission->form,
            'submission' => $submission
        ]);
        $this->trigger(self::EVENT_BEFORE_SEND_ADMIN_EMAIL, $event);
        return $event;
    }

    /**
     * Send user email now
     *
     * @param  SubmissionModel $submission
     * @return bool
     */
    public function sendUserEmail(SubmissionModel $submission, bool $throwException = true): bool
    {
        return $this->getMailService()->sendUserEmail($submission);
    }

    /**
     * Send admin email now
     *
     * @param  SubmissionModel $submission
     * @return bool
     */
    public function sendAdminEmail(SubmissionModel $submission): bool
    {
        return $this->getMailService()->sendAdminEmail($submission);
    }

    /**
     * Sends the user notification email
     *
     * @param  Form             $form
     * @param  SubmissionModel $submission
     * @return bool
     */
    protected function queueUserEmail(Form $form, SubmissionModel $submission): bool
    {
        return $this->getMailService()->queueOrSendUserEmail($form, $submission);
    }

    /**
     * Sends the user notification email(s)
     *
     * @param  Form             $form
     * @param  ?SubmissionModel $submission
     * @return bool
     */
    protected function queueAdminEmail(Form $form, SubmissionModel $submission): bool
    {
        return $this->getMailService()->queueOrSendAdminEmail($form, $submission);
    }

    private function getQueryService(): SubmissionQueryService
    {
        if ($this->queryService === null) {
            $this->queryService = new SubmissionQueryService();
        }

        return $this->queryService;
    }

    private function getMailService(): SubmissionMailService
    {
        if ($this->mailService === null) {
            $this->mailService = new SubmissionMailService($this);
        }

        return $this->mailService;
    }
}
