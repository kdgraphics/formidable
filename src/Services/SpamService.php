<?php 

namespace Inspire\Formidable\Services;

use Inspire\Formidable\Events\SpamEvent;
use Inspire\Formidable\Exceptions\FormEmailException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\Jobs\SendSpamEmailJob;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormSubmission;
use Inspire\Formidable\Models\Records\Spam;
use Inspire\Formidable\Models\Spam as SpamModel;
use Inspire\Formidable\Providers\SpamProvider;
use craft\base\Component;
use craft\helpers\DateTimeHelper;
use craft\helpers\Queue;
use yii\base\Event;
use yii\data\ActiveDataProvider;

class SpamService extends Component
{
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
		$spam = new SpamModel([
			'form_id' => $form->id,
			'ip' => \Craft::$app->request->userIP,
			'values' => json_encode($values),
			'reason' => $submission->_spam_reason ?? ''
		]);
        if ($form->saveSpam) {
        	$record = new Spam([
				'form_id' => $form->id,
				'ip' => \Craft::$app->request->userIP,
				'values' => json_encode($values),
				'reason' => $submission->_spam_reason ?? ''
			]);
    		$record->save(false);
    		$spam = $record->toModel();
        }
        $this->_sendEmail($spam, $submission);
        return $spam;
	}

	public function sendEmail(SpamModel $spam, array $submission): bool
	{
		$emails = [];
        foreach (explode(',', trim($spam->form->notifySpamEmails)) as $address) {
            $address = trim($address);
            $address = \Craft::parseEnv($address);
            if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $address;
            }
        }
        $email = \Craft::$app->getMailer()
            ->composeFromKey($spam->form->notifySpam_key, [
                'spam' => $spam,
                'formSubmission' => $submission,
                'form' => $spam->form
            ])->setTo($emails);

        if ($email->send()) {
            if ($spam->id) {
                $record = $this->getRecordById($spam->id);
                $record->notified = 1;
                $record->save(false);
            }
            return true;
        }
        throw new FormEmailException("Unable to send admin email");
	}

	/**
	 * Send the spam notification
	 * 
	 * @param  Form           $form
	 * @param  ?SpamModel     $spam
	 * @param  FormSubmission $submission
	 * @return bool
	 */
	protected function _sendEmail(SpamModel $spam, FormSubmission $submission): bool
	{
        if (!$spam->form->notifySpam_key) {
            return false;
        }
        if (Formidable::$plugin->settings->useQueue) {
        	Queue::push(new SendSpamEmailJob([
            	'formSubmission' => $submission->toArray(),
            	'spam' => $spam
        	]));
        	return true;
        }
        return $this->sendEmail($spam, $submission->toArray());
	}

	/**
	 * Get some spam, filtered and pagered
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
	 * Get all spams, filtered
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
     * Get spam record by id
     * 
     * @param  int $id
     * @return ?Spam
     */
    public function getRecordById(int $id): ?Spam
    {
        $record = Spam::find()->with('form')->where(['id' => $id])->one();
        return $record ?: null;
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
	 * Run garbage collection
	 */
	public function runGc()
	{
		$threshold = Formidable::$plugin->settings->deleteSpamThreshold;
		if (!$threshold) {
			return;
		}
		$date = (new \DateTime)->sub(new \DateInterval('P' . $threshold . 'D'));
		Spam::deleteAll(['<', 'dateCreated', $date->format('Y-m-d H:i:s')]);
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
	 * Filters the spam data provider
	 * 
	 * @param  array        $filters
	 * @param  ?array       $sort
	 * @return SpamProvider
	 */
	protected function filteredDataProvider(array $filters, ?array $sort): SpamProvider
	{
		$provider = new SpamProvider(['trashed' => $filters['trashed'] ?? false]);
		
		if (isset($filters['forms']) and is_array($filters['forms'])) {
			$provider->query->andWhere(['form_id' => $filters['forms']]);
		}
		if (isset($filters['dateFrom']['date']) and $filters['dateFrom']['date']) {
			$date = $this->parseDateFilter($filters['dateFrom']);
			$provider->query->andWhere(['>=', 'dateCreated', $date]);
		}
		if (isset($filters['dateTo']['date']) and $filters['dateTo']['date']) {
			$date = $this->parseDateFilter($filters['dateTo'], [23,59,59]);
			$provider->query->andWhere(['<=', 'dateCreated', $date]);
		}
		if (isset($filters['ip']) and $filters['ip'] != '') {
			$provider->query->andWhere(['like', 'ip', $filters['ip']]);
		}
		if ($sort && strpos($sort['field'], 'form.') !== false) {
			$provider->query->with(['form' => function ($query) use ($sort) {
				$elems = explode('.', $sort['field']);
				$query->orderBy([$elems[1] => $sort['side'] == 'asc' ? SORT_ASC : SORT_DESC]);
			}]);
		} else {
			$provider->query->with('form');
			$provider->query->orderBy([$sort['field'] => $sort['side'] == 'asc' ? SORT_ASC : SORT_DESC]);
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