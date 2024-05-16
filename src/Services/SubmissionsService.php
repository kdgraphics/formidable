<?php

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Events\EmailEvent;
use Ryssbowh\Formidable\Exceptions\FormEmailException;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Jobs\SendAdminEmailJob;
use Ryssbowh\Formidable\Jobs\SendUserEmailJob;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Notification;
use Ryssbowh\Formidable\Models\Records\Form as FormRecord;
use Ryssbowh\Formidable\Models\Records\Submission;
use Ryssbowh\Formidable\Models\Records\SubmissionValue;
use Ryssbowh\Formidable\Models\Submission as SubmissionModel;
use Ryssbowh\Formidable\Models\SubmissionValue as SubmissionValueModel;
use Ryssbowh\Formidable\Providers\SubmissionProvider;
use Ryssbowh\Formidable\Services\NotificationsService;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Queue;
use craft\mail\Message;
use yii\base\Event;
use yii\data\ActiveDataProvider;

class SubmissionsService extends Component
{
    public const EVENT_BEFORE_SEND_USER_EMAIL = 'before-send-user-email';
    public const EVENT_BEFORE_SEND_ADMIN_EMAIL = 'before-send-admin-email';

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
            'siteId' => \Craft::$app->getSites()->currentSite->id,
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
        $this->_sendUserEmail($form, $submission);
        $this->_sendAdminEmail($form, $submission);
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
        if ($submission = Submission::findOne(['id' => $id])) {
            if ($submission->dateDeleted) {
                return $submission->delete();
            }
            return $submission->softDelete();
        }
        return false;
    }

    /**
     * Run garbage collection
     */
    public function runGc()
    {
        $threshold = Formidable::$plugin->settings->deleteSubmissionsThreshold;
        if (!$threshold) {
            return;
        }
        $date = (new \DateTime())->sub(new \DateInterval('P' . $threshold . 'D'));
        Submission::deleteAll(['<', 'dateCreated', $date->format('Y-m-d H:i:s')]);
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
        $provider = $this->filteredDataProvider($filters, $sort);
        return Formidable::$plugin->csvExporter->exportFromQuery($provider->query);
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
        $provider = $this->filteredDataProvider($filters, $sort);
        $provider->setPagination([
            'defaultPageSize' => $pageSize,
            'page' => $page
        ]);

        return $provider;
    }

    /**
     * Get some submissions, filtered
     *
     * @param  array   $filters
     * @return ActiveDataProvider
     */
    public function getAll(array $filters = []): ActiveDataProvider
    {
        $provider = $this->filteredDataProvider($filters, ['field' => 'dateCreated', 'side' => 'asc']);
        $provider->setPagination(false);

        return $provider;
    }

    /**
     * Build data for a chart js instance
     *
     * @param  array  $models
     * @return array
     */
    public function normalizeDataForChart(array $models): array
    {
        $forms = [];
        $labels = [];
        foreach ($models as $model) {
            $label = $model->dateCreated->format('d/m/Y');
            if (!in_array($label, $labels)) {
                $labels[] = $label;
            }
            if (!in_array($model->form_id, $forms)) {
                $forms[$model->form_id] = [
                    'label' => $model->form->name,
                    'fill' => false,
                    'borderColor' => $model->form->color
                ];
            }
        }
        foreach ($forms as $id => $form) {
            $forms[$id]['data'] = array_map(function () {
                return 0;
            }, $labels);
        }
        foreach ($models as $model) {
            $label = $model->dateCreated->format('d/m/Y');
            $index = array_search($label, $labels);
            $forms[$model->form_id]['data'][$index] ++;
        }
        return [
            'labels' => $labels,
            'data' => array_values($forms)
        ];
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
        $recipient = $submission->getValue($submission->form->notifyField);
        $email = \Craft::$app->getMailer()
            ->composeFromKey($submission->form->notify_key, [
                'submission' => $submission,
                'form' => $submission->form
            ])->setTo($recipient);
        $this->attachSubmissionFiles($email, $submission, 'user');
        $event = $this->sendBeforeUserEmailEvent($email, $submission);
        if (!$event->isValid) {
            return false;
        }
        if ($email->send()) {
            if ($submission->id) {
                $record = $this->getRecordById($submission->id);
                $record->notified = 1;
                $record->save(false);
            }
            return true;
        }
        throw new FormEmailException("Unable to send user email");
    }

    /**
     * Send admin email now
     *
     * @param  SubmissionModel $submission
     * @return bool
     */
    public function sendAdminEmail(SubmissionModel $submission): bool
    {
        $emails = [];
        foreach (explode(',', trim($submission->form->notifyEmails)) as $address) {
            $address = trim($address);
            $address = \Craft::parseEnv($address);
            if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $address;
            }
        }
        $email = \Craft::$app->getMailer()
            ->composeFromKey($submission->form->notifyAdmin_key, [
                'submission' => $submission,
                'form' => $submission->form
            ])->setTo($emails);
        $this->attachSubmissionFiles($email, $submission, 'admin');
        $event = $this->sendBeforeAdminEmailEvent($email, $submission);
        if (!$event->isValid) {
            return false;
        }
        if ($email->send()) {
            if ($submission->id) {
                $record = $this->getRecordById($submission->id);
                $record->adminNotified = 1;
                $record->save(false);
            }
            return true;
        }
        throw new FormEmailException("Unable to send admin email");
    }

    /**
     * Attach submission files to an email if they were marked as such on the form config
     * $type must be 'user' or 'admin'
     *
     * @param Message         $email
     * @param SubmissionModel $submission
     * @param string          $type
     */
    protected function attachSubmissionFiles(Message $email, SubmissionModel $submission, string $type)
    {
        $fileFields = $submission->form->getFieldsByType('file');
        $assets = [];
        foreach ($fileFields as $field) {
            if ($type == 'user' and !$field->attachToUserEmail) {
                continue;
            }
            if ($type == 'admin' and !$field->attachToAdminEmail) {
                continue;
            }
            $assets = array_merge($assets, $field::getAssets($submission->getValue($field->handle)));
        }
        foreach ($assets as $asset) {
            $path = \Craft::getAlias($asset->fs->path . '/' . $asset->getPath($asset->getFilename(true)));
            $email->attach($path);
        }
    }

    /**
     * Filters the submission data provider
     *
     * @param  array $filters
     * @param  array $sort
     * @return SubmissionProvider
     */
    protected function filteredDataProvider(array $filters, array $sort): SubmissionProvider
    {
        $provider = new SubmissionProvider(['trashed' => $filters['trashed'] ?? false]);

        if (isset($filters['forms']) and is_array($filters['forms'])) {
            $provider->query->andWhere(['formidable_submissions.form_id' => $filters['forms']]);
        }
        if (isset($filters['read']) and $filters['read'] !== -1) {
            $provider->query->andWhere(['formidable_submissions.read' => $filters['read']]);
        }
        if (isset($filters['dateFrom']['date']) and $filters['dateFrom']['date']) {
            $date = $this->parseDateFilter($filters['dateFrom']);
            $provider->query->andWhere(['>=', 'formidable_submissions.dateCreated', $date]);
        }
        if (isset($filters['dateTo']['date']) and $filters['dateTo']['date']) {
            $date = $this->parseDateFilter($filters['dateTo'], [23,59,59]);
            $provider->query->andWhere(['<=', 'formidable_submissions.dateCreated', $date]);
        }
        if ($sort && strpos($sort['field'], 'form.') !== false) {
            $provider->query->with(['form' => function ($query) use ($sort) {
                $elems = explode('.', $sort['field']);
                $query->orderBy([FormRecord::tableName() . '.' . $elems[1] => $sort['side'] == 'asc' ? SORT_ASC : SORT_DESC]);
            }]);
        } else {
            $provider->query->orderBy([Submission::tableName() . '.' . $sort['field'] => $sort['side'] == 'asc' ? SORT_ASC : SORT_DESC]);
        }
        if ($filters['search']) {
            $search = '%' . preg_replace('/\s+/', '%', $filters['search']) . '%';
            $provider->query->leftJoin(SubmissionValue::tableName(), SubmissionValue::tableName() . '.submission_id = ' . Submission::tableName() . '.id')
                ->andWhere(['LIKE', SubmissionValue::tableName() . '.value', $search, false]);
        }
        // dd($provider->query->createCommand()->getRawSql());
        return $provider;
    }

    /**
     * Sends the user notification email
     *
     * @param  Form             $form
     * @param  SubmissionModel $submission
     * @return bool
     */
    protected function _sendUserEmail(Form $form, SubmissionModel $submission): bool
    {
        if (!$form->notify_key) {
            return false;
        }
        if (!filter_var($submission->getValue($form->notifyField), FILTER_VALIDATE_EMAIL)) {
            Formidable::log("Can't notify user for form {$form->handle} : Value {$submission->getValue($form->notifyField)} for field {$form->notifyField} is not a valid email");
            return false;
        }

        if (Formidable::$plugin->settings->useQueue) {
            Queue::push(new SendUserEmailJob([
                'submission' => $submission
            ]));
            return true;
        }
        return $this->sendUserEmail($submission);
    }

    /**
     * Sends the user notification email(s)
     *
     * @param  Form             $form
     * @param  ?SubmissionModel $submission
     * @return bool
     */
    protected function _sendAdminEmail(Form $form, SubmissionModel $submission): bool
    {
        if (!$form->notifyAdmin_key) {
            return false;
        }
        if (Formidable::$plugin->settings->useQueue) {
            Queue::push(new SendAdminEmailJob([
                'submission' => $submission
            ]));
            return true;
        }
        return $this->sendAdminEmail($submission);
    }

    /**
     * Parses a posted date into a valid mysql date
     *
     * @param array $value
     * @param array $time
     */
    protected function parseDateFilter(array $value, array $time = [0, 0, 0]): string
    {
        $value['date'] = \DateTime::createFromFormat('d/m/Y', $value['date'])->format('Y-m-d');
        $date = DateTimeHelper::toDateTime($value);
        $date->setTime(...$time);
        return $date->format('Y-m-d H:i:s');
    }
}
