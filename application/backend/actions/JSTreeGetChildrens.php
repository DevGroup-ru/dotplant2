<?php

namespace app\backend\actions;

use app;
use yii;
use yii\base\Action;
use yii\base\InvalidConfigException;

class JSTreeGetChildrens extends Action
{
    public $modelName = null;
    public $viewFile = '_childrens';

    public function init()
    {
        if (!isset($this->modelName)) {
            throw new InvalidConfigException("Model name should be set in controller actions");
        }
        if (!class_exists($this->modelName)) {
            throw new InvalidConfigException("Model class does not exists");
        }
    }

    public function run()
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_RAW;

        $current = Yii::$app->request->get('id');
        $modelName = $this->modelName;
        if (null === $model = (new $modelName)->find()->where(['id' => $current])->one()) {
            return;
        }

        $searchModel = new $modelName;
        $searchModel->parent_id = $model->id;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->controller->renderPartial($this->viewFile, [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }
}
