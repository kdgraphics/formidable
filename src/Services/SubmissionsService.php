<?php 

namespace Inspire\Formidable\Services;

use Inspire\Formidable\Events\EmailEvent;
use Inspire\Formidable\Exceptions\FormEmailException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\Jobs\SendAdminEmailJob;
use Inspire\Formidable\Jobs\SendUserEmailJob;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormPosted;
use Inspire\Formidable\Models\Notification;
use Inspire\Formidable\records\FormRecord;
use Inspire\Formidable\records\SubmissionRecord;
use Inspire\Formidable\records\SubmissionValueRecord;
use Inspire\Formidable\Models\Submission;
use Inspire\Formidable\Models\SubmissionValue;
use Inspire\Formidable\Providers\SubmissionProvider;
use Inspire\Formidable\Services\NotificationsService;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\helpers\Queue;
use craft\mail\Message;
use yii\base\Event;
use yii\data\ActiveDataProvider;

class SubmissionsService extends Component
{
	const EVENT_BEFORE_SEND_USER_EMAIL = 'before-send-user-email';
	const EVENT_BEFORE_SEND_ADMIN_EMAIL = 'before-send-admin-email';

	/**
	 * Creates a submission from a posted form
	 * 
	 * @param  FormPosted $posted
	 * @return ?Submission
	 */
	public function create(FormPosted $posted): ?Submission
	{
		$submission = new Submission([
			'form' => $posted->form
		]);
		$values = [];
		foreach ($posted->form->fields as $field) {
			$values[] = new SubmissionValue([
				'field_id' => $field->id,
				'value' => Json::encode($posted->{$field->handle}),
				'type' => $field->handle()
			]);
		}
		$submission->values = $values;
		if ($posted->form->saveSubmissions) {
			$this->saveSubmission($submission);
		}
		try {
			$this->_sendUserEmail($submission);
		} catch (FormEmailException $e) {
			\Craft::$app->errorHandler->logException($e);
		}
		try {
			$this->_sendAdminEmail($submission);
		} catch (FormEmailException $e) {
			\Craft::$app->errorHandler->logException($e);
		}
		return $submission;
	}

	/**
     * Get submission model by id
     * 
     * @param  int $id
     * @return ?Submission
     */
	public function getById(int $id): ?Submission
	{
		$record = SubmissionRecord::find()->where(['id' => $id])->one();
		return $record ? $record->toModel() : null;
	}

	/**
	 * Get all values for a submission
	 * 
	 * @param  Submission $submission
	 * @return array
	 */
	public function getValuesForSubmission(Submission $submission): array
	{
		return array_map(function ($sub) {
			return $sub->toModel();
		}, SubmissionValueRecord::find()->where(['submission_id' => $submission->id])->all());
	}

	/**
	 * Saves a submission
	 * 
	 * @param  Submission $submission
	 */
	public function saveSubmission(Submission $submission)
	{
		$record = $submission->id ? $this->getRecordById($submission->id) : new SubmissionRecord;
		$record->setAttributes($submission->getAttributes(['form_id', 'read', 'adminNotified', 'notified']), false);
		$record->save(false);
		$submission->setAttributes($record->attributes);
		foreach ($submission->values as $value) {
			$record = $value->id ? $this->getValueRecordById($value->id) : new SubmissionValueRecord;
			$record->setAttributes($value->getAttributes(['type', 'field_id', 'value']), false);
			$record->submission_id = $submission->id;
			$record->save(false);
			$value->setAttributes($record->attributes);
		}
	}

	/**
     * Get submission record by id
     * 
     * @param  int $id
     * @return SubmissionRecord
     */
	public function getRecordById(int $id): SubmissionRecord
	{
		return SubmissionRecord::find()->where(['id' => $id])->one() ?? new SubmissionRecord;
	}

	/**
     * Get submission value record by id
     * 
     * @param  int $id
     * @return SubmissionValueRecord
     */
	public function getValueRecordById(int $id): SubmissionValueRecord
	{
		return SubmissionValueRecord::find()->where(['id' => $id])->one() ?? new SubmissionValueRecord;
	}

	/**
	 * Counts the amount os unread submissions
	 * 
	 * @return int
	 */
	public function countUnread(): int
	{
		return SubmissionRecord::find()
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
		if ($submission = SubmissionRecord::findOne(['id' => $id])) {
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
		if (\Craft::$app->gc->deleteAllTrashed) {
			$threshold = \Craft::$app->config->general->softDeleteDuration;
			$date = (new \DateTime)->sub(new \DateInterval('PT' . $threshold . 'S'));
			SubmissionRecord::deleteAll(['<', 'dateDeleted', $date->format('Y-m-d H:i:s')]);
		}
		$threshold = Formidable::$plugin->settings->deleteSubmissionsThreshold;
		if (!$threshold) {
			return;
		}
		$date = (new \DateTime)->sub(new \DateInterval('P' . $threshold . 'D'));
		$submissions = SubmissionRecord::find()->where(['<', 'dateCreated', $date->format('Y-m-d H:i:s')])->all();
		foreach ($submissions as $submission) {
			$submission->softDelete();
		}
	}

	/**
	 * Delete many submissions by ids
	 * 
	 * @param  array  $ids
	 */
	public function deleteMany(array $ids)
	{
		$_this = $this;
		array_map(function($id) use ($_this) {
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
		$submissions = SubmissionRecord::find()->where(['id' => $ids])->all();
		foreach ($submissions as $sub) {
			$sub->read = 1;
			$sub->save(false);
		}
		return true;
	}

	/**
	 * Export all submissions as csv
	 * @return ?string
	 */
	public function exportAll(): ?string
	{
		$query = SubmissionRecord::find();
		return Formidable::$plugin->csvExporter->exportFromQuery($query);
	}

	/**
	 * Export submissions as csv
	 * @param  array  $ids
	 * @return ?string
	 */
	public function exportAsCsv(array $ids): ?string
	{
		$query = SubmissionRecord::find()->where(['in', 'id', $ids]);
		return Formidable::$plugin->csvExporter->exportFromQuery($query);
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
	 * Send user email now
	 * 
	 * @param  Submission $submission
	 * @return bool
	 */
	public function sendUserEmail(Submission $submission): bool
	{
		$recipient = $submission->getValue($submission->form->notifyField)->asString();
		$form = $submission->form;
		if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
			throw new FormEmailException(\Craft::t('formidable', "Can't notify user for form {form} : Value {value} for field {field} is not a valid email", ['form' => $form->name, 'value' => $recipient, 'field' => $form->notifyField]));
		}
		$email = \Craft::$app->getMailer()
            ->composeFromKey($submission->form->notify_key, [
                'submission' => $submission,
                'form' => $form
            ])->setTo($recipient);
        $this->attachSubmissionFiles($email, $submission, 'user');
		$event = new EmailEvent([
			'email' => $email,
			'form' => $form,
			'submission' => $submission
		]);
		$this->trigger(self::EVENT_BEFORE_SEND_USER_EMAIL, $event);
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
        throw new FormEmailException("Unable to send the user email for submission " . $submission->id);
	}

	/**
	 * Send admin email now
	 * 
	 * @param  Submission $submission
	 * @return bool
	 */
	public function sendAdminEmail(Submission $submission): bool
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
        $event = new EmailEvent([
			'email' => $email,
			'form' => $submission->form,
			'submission' => $submission
		]);
		$this->trigger(self::EVENT_BEFORE_SEND_ADMIN_EMAIL, $event);
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
        throw new FormEmailException("Unable to send the admin email for submission " . $submission->id);
	}

	/**
	 * Sends the user notification email
	 * 
	 * @param  Submission $submission
	 * @return bool
	 */
	protected function _sendUserEmail(Submission $submission): bool
	{
		$form = $submission->form;
		if (!$submission->form->notify_key) {
			return false;
		}
		if ($submission->id and Formidable::$plugin->settings->useQueue) {
			Queue::push(new SendUserEmailJob([
				'submissionId' => $submission->id
			]));
			return true;
		}
		return $this->sendUserEmail($submission);
	}

	/**
	 * Sends the user notification email(s)
	 * 
	 * @param  Submission $submission
	 * @return bool
	 */
	protected function _sendAdminEmail(Submission $submission): bool
	{
		if (!$submission->form->notifyAdmin_key) {
			return false;
		}
		if ($submission->id and Formidable::$plugin->settings->useQueue) {
			Queue::push(new SendAdminEmailJob([
				'submissionId' => $submission->id
			]));
			return true;
		}
		return $this->sendAdminEmail($submission);
	}

	/**
	 * Attach submission files to an email if they were marked as such on the form config
	 * $type must be 'user' or 'admin'
	 * 
	 * @param Message   $email
	 * @param Submission $submission
	 * @param string     $type
	 */
	protected function attachSubmissionFiles(Message $email, Submission $submission, string $type)
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
            $path = \Craft::getAlias($asset->volume->path . '/' . $asset->getPath($asset->getFilename(true)));
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
			$provider->query->andWhere(['form_id' => $filters['forms']]);
		}
		if (isset($filters['read']) and $filters['read'] !== -1) {
			$provider->query->andWhere(['read' => $filters['read']]);
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
			$provider->query->orderBy([SubmissionRecord::tableName() . '.' . $sort['field'] => $sort['side'] == 'asc' ? SORT_ASC : SORT_DESC]);
		}
		if ($filters['search']) {
			$search = '%' . preg_replace('/\s+/', '%', $filters['search']) . '%';
			$provider->query->leftJoin(SubmissionValueRecord::tableName(), SubmissionValueRecord::tableName() . '.submission_id = ' . SubmissionRecord::tableName() . '.id')
				->andWhere(['LIKE', SubmissionValueRecord::tableName() . '.value', $search, false]);
		}
		// dd($provider->query->createCommand()->getRawSql());
        return $provider;
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
