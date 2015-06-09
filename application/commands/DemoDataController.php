<?php

namespace app\commands;

use app\components\Helper;
use app\modules\shop\models\Category;
use yii\console\Controller;

class DemoDataController extends Controller
{
    public function actionIndex()
    {

    }

    private function createCategory($name, $parent_id)
    {
        $model = new Category();
        $model->loadDefaultValues();
        $model->name = $name;
        $model->parent_id = $parent_id;
        $model->slug = Helper::createSlug($model->name);
        $model->save();
        return $model;
    }
}