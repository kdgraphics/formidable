<?php 

namespace Ryssbowh\Formidable\Controllers;

use Ryssbowh\Formidable\Assets\SubmissionsAsset;
use Ryssbowh\Formidable\Models\Records\Submission;
use yii\web\BadRequestHttpException;
use yii\widgets\LinkPager;

class CpSubmissionsController extends Controller
{
	public function actionIndex()
	{
        $this->view->registerAssetBundle(SubmissionsAsset::class);

        return $this->renderTemplate('formidable/cp/submissions/index', [
            'forms' => $this->forms()->getAllAsArray()
        ]);
	}

    public function actionLoad()
    {
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
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $filters = $this->request()->getBodyParam('filters', []);
        $dataProvider = $this->submissions()->getAll($filters);

        return $this->asJson([
            'submissions' => $dataProvider->getModels(),
            'totalCount' => $dataProvider->getTotalCount()
        ]);
    }

    public function actionView(int $id)
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        
        $submission = $this->submissions()->view($id);
        if (!$submission) {
            throw new BadRequestHttpException(\Craft::t('formidable', "Submission with id {id} doesn't exist", ['id' => $id]));
        }

        $response = [
            'submission' => $submission,
            'html' => $this->renderTemplate('formidable/cp/submissions/view', ['submission' => $submission])->data
        ];
        return $this->asJson($response);
    }

    public function actionDelete(int $id)
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        
        $success = $this->submissions()->delete($id);
        if (!$success) {
            throw new BadRequestHttpException(\Craft::t('formidable', "Error while deleting submission {id}", ['id' => $id]));
        }

        $response = [
            'success' => true,
            'message' => \Craft::t('formidable', 'Submission deleted')
        ];
        return $this->asJson($response);
    }

    public function actionDeletemany()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $ids = $this->request()->getRequiredParam('ids');
        $this->submissions()->deleteMany($ids);
        $response = [
            'success' => true,
            'message' => \Craft::t('formidable', 'Submissions deleted')
        ];
        return $this->asJson($response);
    }

    public function actionExport()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $ids = $this->request()->getRequiredParam('ids');
        $response = [
            'success' => true,
            'data' => $this->submissions()->exportAsCsv($ids)
        ];
        return $this->asJson($response);
    }

    public function actionExportAll()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $response = [
            'success' => true,
            'data' => $this->submissions()->exportAll()
        ];
        return $this->asJson($response);
    }
}