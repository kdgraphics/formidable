<?php

namespace Inspire\Formidable\Controllers;

use yii\web\BadRequestHttpException;

class CpUtilitiesController extends Controller
{
    public function actionCategoryGroups()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        
        $groups = [];
        foreach (\Craft::$app->categories->getAllGroups() as $group) {
            $groups[$group->uid] = $group->name;
        }

        return $this->asJson([
            'groups' => $groups
        ]);
    }

    public function actionSections()
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

    public function actionVolumes()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        
        $volumes = array_map(function($volume) {
            return ['uid' => $volume->uid, 'name' => $volume->name];
        }, \Craft::$app->volumes->getAllVolumes());

        return $this->asJson([
            'volumes' => $volumes
        ]);
    }
}