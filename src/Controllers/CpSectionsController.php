<?php 

namespace Ryssbowh\Formidable\Controllers;

use yii\web\BadRequestHttpException;

class CpSectionsController extends Controller
{
	public function actionIndex()
	{
        $this->requirePostRequest();
        $this->requireAcceptsJson();
		
        $sections = \Craft::$app->sections->getSectionsByType('single');
        $sections = array_merge($sections, \Craft::$app->sections->getSectionsByType('channel'));
        $sections = array_merge($sections, \Craft::$app->sections->getSectionsByType('structure'));
        $out = [];
        foreach ($sections as $section) {
            $out[$section->uid] = $section->name;
        }

        return $this->asJson([
            'sections' => $out
        ]);
	}
}