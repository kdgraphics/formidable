<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Events\EmailEvent;
use Ryssbowh\Formidable\Events\SpamEvent;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Records\Spam;
use Ryssbowh\Formidable\Models\Spam as SpamModel;
use Ryssbowh\Formidable\Providers\SpamProvider;
use Ryssbowh\Formidable\Services\NotificationsService;
use craft\base\Component;
use craft\helpers\DateTimeHelper;
use yii\base\Event;
use yii\data\ActiveDataProvider;

class SpamService extends Component
{
	const EVENT_ON_SPAM = 'on_spam';

	/**
	 * Marks spam as notified afte email is sent
	 * 
	 * @param  Spam   $spam 
	 */
	public function updateSpamAfterEmailSent(Spam $spam)
	{
		$spam->notified = 1;
        $spam->save(false);
	}

	/**
	 * Create a spam record
	 * 
	 * @param  Form           $form
	 * @param  FormSubmission $submission
	 * @param  array          $values
	 * @return SpamModel
	 */
	public function create(Form $form, FormSubmission $submission, array $values): SpamModel
	{
		if (isset($values['CRAFT_CSRF_TOKEN'])) {
			unset($values['CRAFT_CSRF_TOKEN']);
		}
		$spam = new Spam([
			'form_id' => $form->id,
			'ip' => \Craft::$app->request->userIP,
			'values' => json_encode($values)
		]);
		$spam->save(false);
		$this->trigger(self::EVENT_ON_SPAM, new SpamEvent([
            'spam' => $spam,
            'form' => $form
        ]));
        $this->sendSpamNotification($form, $spam, $submission);
        return $spam->toModel();
	}

	/**
	 * Send the spam notification
	 * 
	 * @param  Form           $form
	 * @param  Spam           $spam
	 * @param  FormSubmission $submission
	 * @return bool
	 */
	protected function sendSpamNotification(Form $form, Spam $spam, FormSubmission $submission): bool
	{
		if (!$notification = $form->getSpamNotify()) {
        	return false;
        }
        return Formidable::$plugin->notifications->send($notification, $submission, $spam, explode(',', $form->notifyEmails), 'spam');
	}

	/**
	 * Get some spam, filtered and pagered
	 * 
	 * @param  array   $filters 
	 * @param  integer $page
	 * @param  integer $pageSize
	 * @return ActiveDataProvider
	 */
	public function get(array $filters = [], $page = 0, $pageSize = 10): ActiveDataProvider
	{
		$provider = new SpamProvider;
		$provider->setPagination([
			'defaultPageSize' => $pageSize,
			'page' => $page
		]);

		return $this->filterDataProvider($provider, $filters);
	}

	/**
	 * Get some spams, filtered
	 * 
	 * @param  array   $filters 
	 * @return ActiveDataProvider
	 */
	public function getAll(array $filters = []): ActiveDataProvider
	{
		$provider = new SpamProvider;
		$provider->query->orderBy(['dateCreated' => SORT_ASC]);

		return $this->filterDataProvider($provider, $filters);
	}

	/**
     * Get spam model by id
     * 
     * @param  int $id
     * @return ?FormModel
     */
	public function getById(int $id): ?SpamModel
	{
		$record = Spam::find()->with('form')->where(['id' => $id])->one();
		return $record ? $record->toModel() : null;
	}

	/**
	 * Delete a spam by id
	 * 
	 * @param  int    $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		if ($submission = Spam::findOne(['id' => $id]) and $submission->delete()) {
			return true;
		}
		return false;
	}

	/**
	 * Delete many spam by ids
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
	 * Filters the spam data provider
	 * 
	 * @param  SpamProvider $provider
	 * @param  array              $filters
	 * @return SpamProvider
	 */
	protected function filterDataProvider(SpamProvider $provider, array $filters): SpamProvider
	{
		if (isset($filters['form']) and is_numeric($filters['form'])) {
			$provider->query->andWhere(['form_id' => $filters['form']]);
		}
		if (isset($filters['ip']) and $filters['ip'] != '') {
			$provider->query->andWhere(['like', 'ip', '%'.$filters['ip'].'%']);
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