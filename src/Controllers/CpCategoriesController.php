<?php 

namespace Ryssbowh\Formidable\Controllers;

use yii\web\BadRequestHttpException;

class CpCategoriesController extends Controller
{
	public function actionIndex()
	{
        $this->requirePostRequest();
        $this->requireAcceptsJson();
		
        $groups = [];
        $fields = [];
        foreach (\Craft::$app->categories->getAllGroups() as $group) {
            $groups[$group->handle] = $group->name;
            $elems = $group->getFieldLayout()->getFields();
            $fields2 = ['title' => 'Title'];
            foreach ($elems as $field) {
                $fields2[$field->handle] = $field->name;
            }
            $fields[$group->handle] = $fields2;
        }

        return $this->asJson([
            'groups' => $groups,
            'fields' => $fields
        ]);
	}
}