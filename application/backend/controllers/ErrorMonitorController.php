<?php

namespace app\backend\controllers;

use app\backend\models\ErrorMonitor;
use app\components\SearchModel;
use app\models\ErrorLog;
use app\models\ErrorUrl;
use yii\filters\AccessControl;
use yii\web\Controller;

class ErrorMonitorController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['setting manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new ErrorUrl();
        $dataProvider = $searchModel->search($_GET);
        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel
            ]
        );
    }

    public function actionSearch()
    {
        $searchModel = new ErrorMonitor();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'search',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    public function actionDetails($id)
    {
        $searchModel = new SearchModel(
            [
                'defaultOrder' => ['id' => SORT_DESC],
                'model' => ErrorLog::className(),
                'partialMatchAttributes' => ['info', 'server_vars', 'request_vars'],
            ]
        );
        $searchModel->url_id = $id;
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        return $this->render(
            'details',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

}
