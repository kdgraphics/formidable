<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Events\EmailEvent;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Jobs\SendAdminEmailJob;
use Ryssbowh\Formidable\Jobs\SendUserEmailJob;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Notification;
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
	const EVENT_BEFORE_SEND_USER_EMAIL = 'before-send-user-email';
	const EVENT_BEFORE_SEND_ADMIN_EMAIL = 'before-send-admin-email';

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
			'form_id' => $form->id
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
		$this->sendUserEmail($form, $submission);
		$this->sendAdminEmail($form, $submission);
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
		$record = $submission->id ? $this->getRecordById($submission->id) : new Submission;
		foreach ($submission->attributes as $name => $value) {
			$record->$name = $value;
		}
		$record->save(false);
		$submission->setAttributes($record->attributes);
		foreach ($submission->values as $value) {
			$record = $value->id ? $this->getValueRecordById($value->id) : new SubmissionValue;
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
		return Submission::find()->where(['id' => $id])->one() ?? new Submission;
	}

	/**
     * Get submission value record by id
     * 
     * @param  int $id
     * @return SubmissionValue
     */
	public function getValueRecordById(int $id): SubmissionValue
	{
		return SubmissionValue::find()->where(['id' => $id])->one() ?? new SubmissionValue;
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
	 * Delete many submissions bt ids
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
		$submissions = Submission::find()->where(['id' => $ids])->all();
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
		$query = Submission::find();
		return Formidable::$plugin->csvExporter->exportFromQuery($query);
	}

	/**
	 * Export submissions as csv
	 * @param  array  $ids
	 * @return ?string
	 */
	public function exportAsCsv(array $ids): ?string
	{
		$query = Submission::find()->where(['in', 'id', $ids]);
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
	 * Trigger email event to allow other plugin to modify the email
	 * 
	 * @param  Message         $email
	 * @param  Form            $form
	 * @param  SubmissionModel $submission
	 * @return EmailEvent
	 */
	public function sendBeforeUserEmailEvent(Message $email, Form $form, SubmissionModel $submission): EmailEvent
	{
		$event = new EmailEvent([
			'email' => $email,
			'form' => $form,
			'submission' => $submission
		]);
		$this->trigger(self::EVENT_BEFORE_SEND_USER_EMAIL, $event);
		return $event;
	}

	/**
	 * Trigger email event to allow other plugin to modify the email
	 * 
	 * @param  Message         $email
	 * @param  Form            $form
	 * @param  SubmissionModel $submission
	 * @return EmailEvent
	 */
	public function sendBeforeAdminEmailEvent(Message $email, Form $form, SubmissionModel $submission): EmailEvent
	{
		$event = new EmailEvent([
			'email' => $email,
			'form' => $form,
			'submission' => $submission
		]);
		$this->trigger(self::EVENT_BEFORE_SEND_ADMIN_EMAIL, $event);
		return $event;
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
			$provider->query->andWhere(['>=', 'dateCreated', $date]);
		}
		if (isset($filters['dateTo']['date']) and $filters['dateTo']['date']) {
			$date = $this->parseDateFilter($filters['dateTo'], [23,59,59]);
			$provider->query->andWhere(['<=', 'dateCreated', $date]);
		}
		if ($sort && strpos($sort['field'], 'form.') !== false) {
			$provider->query->with(['form' => function ($query) use ($sort) {
				$elems = explode('.', $sort['field']);
				$query->orderBy([$elems[1] => $sort['side'] == 'asc' ? SORT_ASC : SORT_DESC]);
			}]);
		} else {
			$provider->query->orderBy([$sort['field'] => $sort['side'] == 'asc' ? SORT_ASC : SORT_DESC]);
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
	protected function sendUserEmail(Form $form, SubmissionModel $submission): bool
	{
		if (!$form->notify_key) {
			return false;
		}
		if (!filter_var($submission->getValue($form->notifyField), FILTER_VALIDATE_EMAIL)) {
			Formidable::log("Can't notify user for form {$form->handle} : Value {$submission->getValue($form->notifyField)} for field {$form->notifyField} is not a valid email");
 			return false;
		}

		Queue::push(new SendUserEmailJob([
			'email' => $submission->getValue($form->notifyField),
			'submission' => $submission,
			'form' => $form
		]));
		return true;
	}

	/**
	 * Sends the user notification email(s)
	 * 
	 * @param  Form             $form
	 * @param  ?SubmissionModel $submission
	 * @return bool
	 */
	protected function sendAdminEmail(Form $form, SubmissionModel $submission): bool
	{
		if (!$form->notifyAdmin_key) {
			return false;
		}
		Queue::push(new SendAdminEmailJob([
			'submission' => $submission,
			'form' => $form
		]));
		return true;
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
