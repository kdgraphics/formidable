<?php 

namespace Inspire\Formidable\controllers;

use Inspire\Formidable\assets\SpamAsset;
use yii\web\BadRequestHttpException;
use yii\widgets\LinkPager;

class CpSpamController extends Controller
{
	public function actionIndex()
	{
        $this->requirePermission('manageFormidableSubmissions');
        $this->view->registerAssetBundle(SpamAsset::class);

        return $this->renderTemplate('formidable/cp/spam/index', [
            'forms' => $this->forms()->getAllAsArray()
        ]);
	}

    public function actionLoad()
    {
        $this->requirePermission('manageFormidableSubmissions');
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $filters = $this->request()->getBodyParam('filters', []);
        $page = $this->request()->getBodyParam('page', 0);
        $sort = $this->request()->getBodyParam('sort', null);
        $pageSize = $this->request()->getBodyParam('pageSize', 20);
        $dataProvider = $this->spam()->get($filters, $sort, $page, $pageSize);

        return $this->asJson([
            'spams' => $dataProvider->getModels(),
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
        $dataProvider = $this->spam()->getAll($filters);
        $data = $this->spam()->normalizeDataForChart($dataProvider->getModels());

        return $this->asJson([
            'data' => $data,
            'totalCount' => $dataProvider->getTotalCount()
        ]);
    }

    public function actionDeletemany()
    {
        $this->requirePermission('manageFormidableSubmissions');
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $ids = $this->request()->getRequiredParam('ids');
        $this->spam()->deleteMany($ids);
        $message = \Craft::t('formidable', 'Spams deleted');
        if (sizeof($ids) == 1) {
            $message = \Craft::t('formidable', 'Spam deleted');
        }
        $response = [
            'success' => true,
            'message' => $message
        ];
        return $this->asJson($response);
    }
}
