<?php 

namespace Inspire\Formidable\Services;

use Inspire\Formidable\Events\SpamEvent;
use Inspire\Formidable\Exceptions\FormEmailException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\Jobs\SendSpamEmailJob;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormPosted;
use Inspire\Formidable\Models\Spam;
use Inspire\Formidable\Models\SubmissionValue;
use Inspire\Formidable\Providers\SpamProvider;
use Inspire\Formidable\records\SpamRecord;
use Inspire\Formidable\records\SpamValueRecord;
use craft\base\Component;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\helpers\Queue;
use yii\base\Event;
use yii\data\ActiveDataProvider;

class SpamService extends Component
{
	/**
	 * Create a spam record
	 * 
	 * @param  FormPosted $posted
	 * @return Spam
	 */
	public function create(FormPosted $posted): Spam
	{
		$spam = new Spam([
			'form' => $posted->form,
			'ip' => \Craft::$app->request->userIP,
			'reason' => $posted->spam_reason ?? '',
			'userAgent' => \Craft::$app->request->userAgent
		]);
		$values = [];
		foreach ($posted->form->fields as $field) {
			$values[] = new SubmissionValue([
				'field_id' => $field->id,
				'value' => Json::encode($posted->{$field->handle}),
				'type' => $field->handle()
			]);
		}
		$spam->values = $values;

        if ($posted->form->saveSpam) {
        	$this->saveSpam($spam);
        }
        try {
			$this->_sendEmail($spam);
		} catch (FormEmailException $e) {
			\Craft::$app->errorHandler->logException($e);
		}
        return $spam;
	}

	public function sendEmail(Spam $spam): bool
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
        throw new FormEmailException("Unable to send the spam email for form " . $spam->form->name);
	}

	/**
	 * Saves a spam
	 * 
	 * @param Spam $spam
	 */
	public function saveSpam(Spam $spam)
	{
		$record = $spam->id ? $this->getRecordById($submission->id) : new SpamRecord;
		$record->setAttributes($spam->getAttributes(['form_id', 'ip', 'notified', 'reason', 'userAgent']), false);
		$record->save(false);
		$spam->setAttributes($record->attributes);
		foreach ($spam->values as $value) {
			$record = $value->id ? $this->getValueRecordById($value->id) : new SpamValueRecord;
			$record->setAttributes($value->getAttributes(['type', 'field_id', 'value']), false);
			$record->spam_id = $spam->id;
			$record->save(false);
			$value->setAttributes($record->attributes);
		}
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
     * @return ?Spam
     */
	public function getById(int $id): ?Spam
	{
		$record = SpamRecord::find()->with('form')->where(['id' => $id])->one();
		return $record ? $record->toModel() : null;
	}

    /**
     * Get spam record by id
     * 
     * @param  int $id
     * @return ?SpamRecord
     */
    public function getRecordById(int $id): ?SpamRecord
    {
        $record = SpamRecord::find()->with('form')->where(['id' => $id])->one();
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
		if ($spam = SpamRecord::findOne(['id' => $id]) and $spam->delete()) {
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
		if (\Craft::$app->gc->deleteAllTrashed) {
			$threshold = \Craft::$app->config->general->softDeleteDuration;
			$date = (new \DateTime)->sub(new \DateInterval('PT' . $threshold . 'S'));
			SpamRecord::deleteAll(['<', 'dateDeleted', $date->format('Y-m-d H:i:s')]);
		}
		$threshold = Formidable::$plugin->settings->deleteSpamThreshold;
		if (!$threshold) {
			return;
		}
		$date = (new \DateTime)->sub(new \DateInterval('P' . $threshold . 'D'));
		$spams = SpamRecord::find()->where(['<', 'dateCreated', $date->format('Y-m-d H:i:s')])->all();
		foreach ($spams as $spam) {
			$spam->softDelete();
		}
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
	 * Get all values for a spam
	 * 
	 * @param  Spam $spam
	 * @return array
	 */
	public function getValuesForSpam(Spam $spam): array
	{
		return array_map(function ($sub) {
			return $sub->toModel();
		}, SpamValueRecord::find()->where(['spam_id' => $spam->id])->all());
	}

	/**
     * Get submission value record by id
     * 
     * @param  int $id
     * @return SpamValueRecord
     */
	public function getValueRecordById(int $id): SpamValueRecord
	{
		return SpamValueRecord::find()->where(['id' => $id])->one() ?? new SpamValueRecord;
	}

	/**
	 * Send the spam notification
	 * 
	 * @param  Spam $spam
	 * @return bool
	 */
	protected function _sendEmail(Spam $spam): bool
	{
        if (!$spam->form->notifySpam_key) {
            return false;
        }
        if ($spam->id and Formidable::$plugin->settings->useQueue) {
        	Queue::push(new SendSpamEmailJob([
            	'spamId' => $spam->id
        	]));
        	return true;
        }
        return $this->sendEmail($spam, $posted->toArray());
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