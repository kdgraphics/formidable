<?php

namespace Inspire\Formidable\services;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\models\Records\Submission;
use Inspire\Formidable\models\Records\SubmissionValue;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use yii\db\Query;

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

        $fields = $this->resolveCsvFields($query);
        $headers = [\Craft::t('formidable', 'Form'), \Craft::t('formidable', 'Status'), \Craft::t('formidable', 'Date created'), \Craft::t('formidable', 'User notified'), \Craft::t('formidable', 'Admin notified')];
        $names = array_map(function ($field) {
            return $field->name;
        }, $fields);
        $fp = fopen("php://temp", 'w');
        fputcsv($fp, array_merge($headers, $names), ',');

        if ($count < 100) {
            $submissions = $query->all();
            $this->exportBatch($fp, $submissions, $fields);
        } else {
            $batches = ceil($count / 100);
            for ($i = 0; $i < $batches; $i++) {
                $submissions = $query->limit($i * 100, 100)->all();
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
            $submission = $submission->toModel();
            $line = [
                $submission->form->name,
                $submission->read ? 'Read' : 'Unread',
                $submission->dateCreated->format('d/m/Y'),
                $submission->notified ? 'Yes' : 'No',
                $submission->adminNotified ? 'Yes' : 'No',
            ];
            foreach ($fields as $field) {
                $line[] = $submission->getValue($field->handle, 'csv');
            }
            fputcsv($fp, $line, ',');
        }
    }

    /**
     * Returns all fields for csv. Because the export might
     * contain several forms with different fields, the headers
     * will be the same for all the forms, just some csv lines
     * will have some blanks, as they don't have values for fields
     * that belong to other forms.
     *
     * @param $query
     * @return array
     */
    protected function resolveCsvFields($query): array
    {
        $values = (new Query())
            ->select('field_id')
            ->where(['is not', 'field_id', null])
            ->from(SubmissionValue::tableName())
            ->leftJoin(Submission::tableName(), Submission::tableName() . '.id = ' . SubmissionValue::tableName() . ' .submission_id');
        if ($query->where) {
            $values->andWhere($query->where);
        }
        $values = $values->distinct()->all();
        return array_map(function ($value) {
            return Formidable::$plugin->fields->getById($value['field_id']);
        }, $values);
    }
}
