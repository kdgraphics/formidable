<?php

namespace Inspire\Formidable\controllers;

use yii\web\BadRequestHttpException;

class CpSectionsController extends Controller
{
    public function actionIndex()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $sections = \Craft::$app->getEntries()->getSectionsByType('single');
        $sections = array_merge($sections, \Craft::$app->getEntries()->getSectionsByType('channel'));
        $sections = array_merge($sections, \Craft::$app->getEntries()->getSectionsByType('structure'));
        $out = [];
        foreach ($sections as $section) {
            $out[$section->uid] = $section->name;
        }

        return $this->asJson([
            'sections' => $out
        ]);
    }
}
