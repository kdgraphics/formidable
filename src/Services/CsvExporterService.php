<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\Records\SubmissionValue;
use craft\base\Component;
use craft\helpers\ArrayHelper;

class CsvExporterService extends Component
{
	/**
	 * Export submissions as csv using a query
	 * 
	 * @param  Query $query
	 * @return ?string
	 */
	public function exportFromQuery($query): ?string
	{
		if ($count = $query->count() == 0) {
			return null;
		}

		$fields = $this->resolveHeaders($query);
		$headers = [\Craft::t('formidable','Form'), \Craft::t('formidable','Status'), \Craft::t('formidable','Date created'), \Craft::t('formidable','User notified'), \Craft::t('formidable','Admin notified')];
		$fp = fopen("php://temp", 'w');
		fputcsv($fp, array_merge($headers, $fields), ',');

		if ($count < 100) {
			$submissions = $query->all();
			$this->exportBatch($fp, $submissions, $fields);
		} else {
			$batches = ceil($count / 100);
			for ($i = 0; $i < $batches; $i++) {
				$submissions = $query->limit($i*100, 100)->all();
				$this->exportBatch($fp, $submissions, $fields);
			}
		}

		rewind($fp);
		return stream_get_contents($fp);
	}

	/**
	 * Export a batch of submissions and write csv in stream
	 * 
	 * @param  stream $fp
	 * @param  array  $submissions
	 * @param  array  $fields
	 */
	protected function exportBatch($fp, array $submissions, array $fields)
	{
		foreach ($submissions as $submission) {
			$line = [
				$submission->form->name,
				$submission->read ? 'Read': 'Unread',
				$submission->dateCreated,
				$submission->notified ? 'Yes' : 'No',
				$submission->adminNotified ? 'Yes' : 'No',
			];
			foreach ($fields as $field) {
				if ($value = ArrayHelper::firstWhere($submission->values, 'field', $field)) {
					if ($fieldInstance = Forms::$plugin->fields->getRegistered($field)) {
						$line[] = $fieldInstance->getCsvValue($value->value);
					} else {
						$line[] = '';
					}
				} else {
					$line[] = '';
				}
			}
			fputcsv($fp, $line, ',');
		}
	}

	/**
	 * Returns all headers for csv. Because the export might
	 * contain several forms with different fields, the headers
	 * will be the same for all the forms, just some csv lines
	 * will have some blanks, as they don't have values for fields
	 * that belong to other forms.
	 * 
	 * @param $query
	 * @return array
	 */
	protected function resolveHeaders($query)
	{
		$values = (new \yii\db\Query())
		    ->select('field')
		    ->from(SubmissionValue::tableName());
		if ($query->where) {
			$where = $query->where;
			$where[1] = 'submission_id';
			$values->where($where);
		}
		$values = $values->distinct()->all();
		return array_map(function ($value) {
			return $value['field'];
		}, $values);
	}
}