<?php

namespace Inspire\Formidable\controllers;

use Inspire\Formidable\Assets\SubmissionsAsset;
use Inspire\Formidable\Models\Records\Submission;
use craft\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\widgets\LinkPager;

class CpSubmissionsController extends Controller
{
	public function actionIndex()
	{
        $this->requirePermission('manageFormidableSubmissions');
        $this->view->registerAssetBundle(SubmissionsAsset::class);

        return $this->renderTemplate('formidable/cp/submissions/index', [
            'forms' => $this->forms()->getAllAsArray(),
            'sites' => $this->submissions()->getSites(),
        ]);
	}

    public function actionLoad()
    {
        $this->requirePermission('manageFormidableSubmissions');
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $filters = $this->request()->getBodyParam('filters', []);
        $sort = $this->request()->getBodyParam('sort', null);
        $page = $this->request()->getBodyParam('page', 0);
        $pageSize = $this->request()->getBodyParam('pageSize', 20);
        $dataProvider = $this->submissions()->get($filters, $sort, $page, $pageSize);

        return $this->asJson([
            'submissions' => $dataProvider->getModels(),
            'count' => $dataProvider->getCount(),
            'totalCount' => $dataProvider->getTotalCount(),
            'page' => $dataProvider->getPagination()->getPage(),
            'pageCount' => $dataProvider->getPagination()->getPageCount()
        ]);
    }

    public function actionLoadAll()
    {
        $this->requirePermission('manageFormidableSubmissions');
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $filters = $this->request()->getBodyParam('filters', []);
        $dataProvider = $this->submissions()->getAll($filters);
        $data = $this->submissions()->normalizeDataForChart($dataProvider->getModels());

        return $this->asJson([
            'data' => $data,
            'totalCount' => $dataProvider->getTotalCount()
        ]);
    }

    public function actionSetAsRead()
    {
        $this->requirePermission('manageFormidableSubmissions');
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $ids = $this->request()->getRequiredParam('ids');
        $this->submissions()->setManyAsRead($ids);
        $response = [
            'success' => true,
            'message' => \Craft::t('formidable', 'Submissions marked as read')
        ];
        return $this->asJson($response);
    }

    public function actionDeletemany()
    {
        $this->requirePermission('manageFormidableSubmissions');
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $ids = $this->request()->getRequiredParam('ids');
        $this->submissions()->deleteMany($ids);
        $message = \Craft::t('formidable', 'Submissions deleted');

        if (sizeof($ids) == 1) {
            $message = \Craft::t('formidable', 'Submission deleted');
        }

        $response = [
            'success' => true,
            'message' => $message
        ];
        
        return $this->asJson($response);
    }

    public function actionExport()
    {
        $this->requirePermission('manageFormidableSubmissions');
        $ids = explode(',', $this->request()->getRequiredParam('ids'));
        $data = $this->submissions()->exportIdsAsCsv($ids);
        return \Craft::$app->getResponse()->sendContentAsFile($data, 'submissions.csv', [
            'mimeType' => 'text/csv'
        ]);
    }

    public function actionExportAll()
    {
        $this->requirePermission('manageFormidableSubmissions');
        $filters = Json::decode($this->request()->getRequiredParam('filters'));
        $sort = Json::decode($this->request()->getRequiredParam('sort'));
        $data = $this->submissions()->exportAllAsCsv($filters, $sort);
        return \Craft::$app->getResponse()->sendContentAsFile($data, 'submissions.csv', [
            'mimeType' => 'text/csv'
        ]);
    }
}
