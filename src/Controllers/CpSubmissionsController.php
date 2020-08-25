<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Assets\SubmissionAsset;
use Inspire\Forms\Models\Records\Submission;
use yii\web\BadRequestHttpException;
use yii\widgets\LinkPager;

class CpSubmissionsController extends Controller
{
	public function actionIndex()
	{
        $this->view->registerAssetBundle(SubmissionAsset::class);

        return $this->renderTemplate('inspireforms/cp/submissions/index', [
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
        $dataProvider = $this->submissions()->get($filters, $page, $pageSize);

        return $this->asJson([
            'submissions' => $dataProvider->getModels(),
            'count' => $dataProvider->getCount(),
            'totalCount' => $dataProvider->getTotalCount(),
            'page' => $dataProvider->getPagination()->getPage(),
            'pageCount' => $dataProvider->getPagination()->getPageCount(),
            'pagination' => LinkPager::widget([
                'pagination' => $dataProvider->getPagination(),
            ])
        ]);
    }

    public function actionView(int $id)
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        
        $submission = $this->submissions()->view($id);
        if (!$submission) {
            throw BadRequestHttpException("Submission with id $id doesn't exist");
        }

        $response = [
            'submission' => $submission,
            'html' => $this->renderTemplate('inspireforms/cp/submissions/view', ['submission' => $submission])->data
        ];
        return $this->asJson($response);
    }

    public function actionDelete(int $id)
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        
        $success = $this->submissions()->delete($id);
        if (!$success) {
            throw BadRequestHttpException("Error while deleting submission $id");
        }

        $response = [
            'success' => true,
            'message' => \Craft::t('inspireforms', 'Submission deleted')
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
            'message' => \Craft::t('inspireforms', 'Submissions deleted')
        ];
        return $this->asJson($response);
    }
}