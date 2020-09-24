<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Events\EmailEvent;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Notification;
use Ryssbowh\Formidable\Models\Records\Submission;
use Ryssbowh\Formidable\Models\Records\SubmissionValue;
use Ryssbowh\Formidable\Models\Submission as SubmissionModel;
use Ryssbowh\Formidable\Providers\SubmissionProvider;
use Ryssbowh\Formidable\Services\NotificationsService;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use yii\base\Event;
use yii\data\ActiveDataProvider;

class SubmissionsService extends Component
{
    /**
     * Updates sent markers on a submission after an email is sent
     * 
     * @param Submission $submission
     * @param string     $type
     */
    public function updateSubmissionAfterEmailSent(Submission $submission, string $type)
    {
    	if ($type == 'user') {
            $submission->notified = 1;
        } elseif($type == 'admin') {
            $submission->adminNotified = 1;
        }
        $submission->save(false);
    }

	/**
	 * Creates a submission
	 * 
	 * @param  Form           $form
	 * @param  FormSubmission $formSubmission
	 * @return ?SubmissionModel
	 */
	public function create(Form $form, FormSubmission $formSubmission): ?SubmissionModel
	{
		if ($form->saveSubmissions) {
			$submission = new Submission([
				'form_id' => $form->id,
				'read' => false,
				'adminNotified' => false,
				'notified' => false
			]);

			if ($submission->save(false)) {
				$this->sendUserEmail($form, $formSubmission, $submission);
				$this->sendAdminEmail($form, $formSubmission, $submission);
			}

			foreach ($form->fields as $field) {
				(new SubmissionValue([
					'submission_id' => $submission->id,
					'field' => $field->name,
					'value' => $formSubmission->{$field->name},
					'type' => $field->handle()
				]))->save(false);
			}
			return $submission->toModel();
		}
		$this->sendUserEmail($form, $formSubmission);
		$this->sendAdminEmail($form, $formSubmission);
		return null;
	}

	/**
     * Get submission model by id
     * 
     * @param  int $id
     * @return ?FormModel
     */
	public function getById(int $id): ?SubmissionModel
	{
		$record = Submission::find()->with('values')->where(['id' => $id])->one();
		return $record ? $record->toModel() : null;
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
	 * View a submission, sets it as read
	 * 
	 * @param  int    $id
	 * @return ?SubmissionModel
	 */
	public function view(int $id): ?SubmissionModel
	{
		if ($submission = $this->getById($id)) {
			$submission->setAsRead();
			return $submission;
		}
		return null;
	}

	/**
	 * Delete a submission by id
	 * 
	 * @param  int    $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		if ($submission = Submission::findOne(['id' => $id]) and $submission->delete()) {
			return true;
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
		$provider = new SubmissionProvider;
		$provider->setPagination([
			'defaultPageSize' => $pageSize,
			'page' => $page
		]);

		return $this->filterDataProvider($provider, $filters, $sort);
	}

	/**
	 * Get some submissions, filtered
	 * 
	 * @param  array   $filters 
	 * @return ActiveDataProvider
	 */
	public function getAll(array $filters = []): ActiveDataProvider
	{
		$provider = new SubmissionProvider;

		return $this->filterDataProvider($provider, $filters, ['field' => 'dateCreated', 'side' => 'asc']);
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
	 * Filters the submission data provider
	 * 
	 * @param  SubmissionProvider $provider
	 * @param  array              $filters
	 * @return SubmissionProvider
	 */
	protected function filterDataProvider(SubmissionProvider $provider, array $filters, ?array $sort): SubmissionProvider
	{
		if (isset($filters['trashed'])) {
			if ($filters['trashed'] == null) {
				$provider->withTrashed();
			} elseif ($filters['trashed'] === true) {
				$provider->trashed();
			}
		}
		if (isset($filters['forms']) and is_array($filters['forms'])) {
			$provider->query->andWhere(['form_id' => $filters['forms']]);
		}
		if (isset($filters['read']) and $filters['read'] !== null) {
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
				$query->orderBy($elems[1], $sort['side'] == 'asc' ? SORT_ASC : SORT_DESC);
			}]);
		} else {
			$provider->query->with('form');
			$provider->query->orderBy($sort['field'], $sort['side'] == 'asc' ? SORT_ASC : SORT_DESC);
		}
		// dd($provider->query->createCommand()->getRawSql());
        return $provider;
	}

	/**
	 * Sends the user notification email
	 * 
	 * @param  Form           $form
	 * @param  FormSubmission $formSubmission
	 * @param  ?Submission    $submission
	 * @return bool
	 */
	protected function sendUserEmail(Form $form, FormSubmission $formSubmission, ?Submission $submission = null): bool
	{
		$notification = $form->getNotify();
		if (!$notification or !$form->notifyField) {
			return false;
		}
		if (!filter_var($formSubmission->{$form->notifyField}, FILTER_VALIDATE_EMAIL)) {
			Formidable::log("Can't notify user for form {$form->handle} : Value {$formSubmission->{$form->notifyField}} for field {$form->notifyField} is not a valid email");
 			return false;
		}
		return Formidable::$plugin->notifications->send($notification, $formSubmission, $submission, [$formSubmission->{$form->notifyField}], 'user');
	}

	/**
	 * Sends the user notification email(s)
	 * 
	 * @param  Form           $form
	 * @param  FormSubmission $formSubmission
	 * @param  Submission     $submission
	 * @return bool
	 */
	protected function sendAdminEmail(Form $form, FormSubmission $formSubmission, ?Submission $submission = null): bool
	{
		if (!$notification = $form->getAdminNotify()) {
			return false;
		}
		return Formidable::$plugin->notifications->send($notification, $formSubmission, $submission, explode(',', $form->notifyEmails), 'admin');
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