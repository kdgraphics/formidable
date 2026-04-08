<?php

namespace Inspire\Formidable\services;

use DateInterval;
use DateTime;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\models\Records\Form as FormRecord;
use Inspire\Formidable\models\Records\Submission;
use Inspire\Formidable\models\Records\SubmissionValue;
use Inspire\Formidable\providers\SubmissionProvider;
use craft\helpers\DateTimeHelper;
use yii\data\ActiveDataProvider;

class SubmissionQueryService
{
    public function get(array $filters = [], ?array $sort = null, int $page = 0, int $pageSize = 10): ActiveDataProvider
    {
        $provider = $this->filteredDataProvider($filters, $sort ?? ['field' => 'dateCreated', 'side' => 'desc']);
        $provider->setPagination([
            'defaultPageSize' => $pageSize,
            'page' => $page,
        ]);

        return $provider;
    }

    public function getAll(array $filters = []): ActiveDataProvider
    {
        $provider = $this->filteredDataProvider($filters, ['field' => 'dateCreated', 'side' => 'asc']);
        $provider->setPagination(false);

        return $provider;
    }

    public function exportAllAsCsv(array $filters, array $sort): ?string
    {
        $provider = $this->filteredDataProvider($filters, $sort);

        return Formidable::$plugin->csvExporter->exportFromQuery($provider->query);
    }

    public function normalizeDataForChart(array $models): array
    {
        $forms = [];
        $labels = [];

        foreach ($models as $model) {
            $label = $model->dateCreated->format('d/m/Y');
            if (!in_array($label, $labels, true)) {
                $labels[] = $label;
            }
            if (!isset($forms[$model->form_id])) {
                $forms[$model->form_id] = [
                    'label' => $model->form->name,
                    'fill' => false,
                    'borderColor' => $model->form->color,
                ];
            }
        }

        foreach (array_keys($forms) as $id) {
            $forms[$id]['data'] = array_map(static fn () => 0, $labels);
        }

        foreach ($models as $model) {
            $label = $model->dateCreated->format('d/m/Y');
            $index = array_search($label, $labels, true);
            $forms[$model->form_id]['data'][$index]++;
        }

        return [
            'labels' => $labels,
            'data' => array_values($forms),
        ];
    }

    public function runGc(): void
    {
        $threshold = Formidable::$plugin->settings->deleteSubmissionsThreshold;
        if (!$threshold) {
            return;
        }

        $date = (new DateTime())->sub(new DateInterval('P' . $threshold . 'D'));
        Submission::deleteAll(['<', 'dateCreated', $date->format('Y-m-d H:i:s')]);
    }

    private function filteredDataProvider(array $filters, array $sort): SubmissionProvider
    {
        $provider = new SubmissionProvider(['trashed' => $filters['trashed'] ?? false]);

        if (isset($filters['forms']) && is_array($filters['forms'])) {
            $provider->query->andWhere(['formidable_submissions.form_id' => $filters['forms']]);
        }
        if (isset($filters['read']) && $filters['read'] !== -1) {
            $provider->query->andWhere(['formidable_submissions.read' => $filters['read']]);
        }
        if (!empty($filters['dateFrom']['date'])) {
            $provider->query->andWhere(['>=', 'formidable_submissions.dateCreated', $this->parseDateFilter($filters['dateFrom'])]);
        }
        if (!empty($filters['dateTo']['date'])) {
            $provider->query->andWhere(['<=', 'formidable_submissions.dateCreated', $this->parseDateFilter($filters['dateTo'], [23, 59, 59])]);
        }
        if (strpos($sort['field'], 'form.') !== false) {
            $provider->query->with(['form' => function ($query) use ($sort) {
                $elements = explode('.', $sort['field']);
                $query->orderBy([FormRecord::tableName() . '.' . $elements[1] => $sort['side'] === 'asc' ? SORT_ASC : SORT_DESC]);
            }]);
        } else {
            $provider->query->orderBy([Submission::tableName() . '.' . $sort['field'] => $sort['side'] === 'asc' ? SORT_ASC : SORT_DESC]);
        }
        if (!empty($filters['search'])) {
            $search = '%' . preg_replace('/\s+/', '%', $filters['search']) . '%';
            $provider->query
                ->leftJoin(SubmissionValue::tableName(), SubmissionValue::tableName() . '.submission_id = ' . Submission::tableName() . '.id')
                ->andWhere(['LIKE', SubmissionValue::tableName() . '.value', $search, false]);
        }
        if (!empty($filters['siteId'])) {
            $provider->query->andWhere(['formidable_submissions.siteId' => $filters['siteId']]);
        }

        return $provider;
    }

    private function parseDateFilter(array $value, array $time = [0, 0, 0]): string
    {
        $value['date'] = \DateTime::createFromFormat('d/m/Y', $value['date'])->format('Y-m-d');
        $date = DateTimeHelper::toDateTime($value);
        $date->setTime(...$time);

        return $date->format('Y-m-d H:i:s');
    }
}
