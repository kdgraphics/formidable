<?php

namespace Inspire\Formidable\Controllers;

use yii\web\BadRequestHttpException;

class CpCategoriesController extends Controller
{
	public function actionIndex()
	{
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $groups = [];
        foreach (\Craft::$app->categories->getAllGroups() as $group) {
            $groups[$group->handle] = $group->name;
        }

        return $this->asJson([
            'groups' => $groups
        ]);
	}
}
