<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Records\Submission;
use Ryssbowh\Formidable\Models\Records\SubmissionValue;
use Ryssbowh\Formidable\Models\Submission as SubmissionModel;
use Ryssbowh\Formidable\Providers\SubmissionProvider;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use yii\data\ActiveDataProvider;

class SubmissionsService extends Component
{
	/**
	 * Creates a submission
	 * 
	 * @param  Form           $form
	 * @param  FormSubmission $formSubmission
	 * @return SubmissionModel
	 */
	public function create(Form $form, FormSubmission $formSubmission): SubmissionModel
	{
		$submission = new Submission([
			'form_id' => $form->id,
			'read' => false,
			'adminNotified' => false,
			'notified' => false
		]);

		if ($submission->save()) {
			$submission->notified = $this->sendUserEmail($form, $formSubmission);
			$submission->adminNotified = $this->sendAdminEmail($form, $formSubmission);
			$submission->save(false);
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
	 * @param  integer $page
	 * @param  integer $pageSize
	 * @return ActiveDataProvider
	 */
	public function get(array $filters = [], $page = 0, $pageSize = 10): ActiveDataProvider
	{
		$provider = new SubmissionProvider;
		$provider->setPagination([
			'defaultPageSize' => $pageSize,
			'page' => $page
		]);

		return $this->filterDataProvider($provider, $filters);
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
		$provider->query->orderBy(['dateCreated' => SORT_ASC]);

		return $this->filterDataProvider($provider, $filters);
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
	protected function filterDataProvider(SubmissionProvider $provider, array $filters): SubmissionProvider
	{
		if (isset($filters['form']) and is_numeric($filters['form'])) {
			$provider->query->andWhere(['form_id' => $filters['form']]);
		}
		if (isset($filters['read']) and $filters['read'] != '') {
			$provider->query->andWhere(['read' => $filters['read']]);
		}
		if (isset($filters['notified'])  and $filters['notified'] != '') {
			$provider->query->andWhere(['notified' => $filters['notified']]);
		}
		if (isset($filters['adminNotified']) and $filters['adminNotified'] != '') {
			$provider->query->andWhere(['adminNotified' => $filters['adminNotified']]);
		}
		if (isset($filters['submittedFrom']['date']) and $filters['submittedFrom']['date'] != '') {
			$date = $this->parseDateFilter($filters['submittedFrom']);
			$provider->query->andWhere(['>=', 'dateCreated', $date]);
		}
		if (isset($filters['submittedFrom']['date']) and $filters['submittedTo']['date'] != '') {
			$date = $this->parseDateFilter($filters['submittedTo'], [23,59,59]);
			$provider->query->andWhere(['<=', 'dateCreated', $date]);
		}

        return $provider;
	}

	/**
	 * Sends the user notification email
	 * 
	 * @param  Form           $form
	 * @param  FormSubmission $formSubmission
	 * @return bool
	 */
	protected function sendUserEmail(Form $form, FormSubmission $formSubmission): bool
	{
		$notification = $form->getNotify();
		if (!$notification or !$form->notifyField) {
			return false;
		}
		if (!filter_var($formSubmission->{$form->notifyField}, FILTER_VALIDATE_EMAIL)) {
			Formidable::log("Can't notify user for form {$form->handle} : Value {$formSubmission->{$form->notifyField}} for field {$form->notifyField} is not a valid email");
 			return false;
		}
		return $notification->send($formSubmission, $formSubmission->{$form->notifyField});
	}

	/**
	 * Sends the user notification email(s)
	 * 
	 * @param  Form           $form
	 * @param  FormSubmission $formSubmission
	 * @return bool
	 */
	protected function sendAdminEmail(Form $form, FormSubmission $formSubmission): bool
	{
		$notification = $form->getAdminNotify();
		if (!$notification) {
			return false;
		}
		return $notification->send($formSubmission, explode(',', $form->notifyEmails));
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