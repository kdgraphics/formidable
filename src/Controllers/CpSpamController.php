<?php 

namespace Ryssbowh\Formidable\Controllers;

use Ryssbowh\Formidable\Assets\SpamAsset;
use yii\web\BadRequestHttpException;
use yii\widgets\LinkPager;

class CpSpamController extends Controller
{
	public function actionIndex()
	{
        $this->view->registerAssetBundle(SpamAsset::class);

        return $this->renderTemplate('formidable/cp/spam/index', [
            'forms' => $this->forms()->getAllAsArray()
        ]);
	}

    public function actionLoad()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $filters = $this->request()->getBodyParam('filters', []);
        $page = $this->request()->getBodyParam('page', 0);
        $pageSize = $this->request()->getBodyParam('pageSize', 0);
        $dataProvider = $this->spam()->get($filters, $page, $pageSize);

        return $this->asJson([
            'spams' => $dataProvider->getModels(),
            'count' => $dataProvider->getCount(),
            'totalCount' => $dataProvider->getTotalCount(),
            'page' => $dataProvider->getPagination()->getPage(),
            'pageCount' => $dataProvider->getPagination()->getPageCount(),
            'pagination' => LinkPager::widget([
                'pagination' => $dataProvider->getPagination(),
            ])
        ]);
    }

    public function actionLoadAll()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $filters = $this->request()->getBodyParam('filters', []);
        $dataProvider = $this->spam()->getAll($filters);

        return $this->asJson([
            'spams' => $dataProvider->getModels(),
            'totalCount' => $dataProvider->getTotalCount()
        ]);
    }

    public function actionView(int $id)
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        
        $spam = $this->spam()->getById($id);
        if (!$spam) {
            throw new BadRequestHttpException(\Craft::t('formidable', "Spam with id {id} doesn't exist", ['id' => $id]));
        }

        $response = [
            'spam' => $spam,
            'html' => $this->renderTemplate('formidable/cp/spam/view', ['spam' => $spam, 'values' => decodeJson($spam->values)])->data
        ];
        return $this->asJson($response);
    }
}