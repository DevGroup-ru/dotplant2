<?php

namespace app\backend\controllers;

use app\backend\models\ApiService;
use yii\filters\AccessControl;
use yii\web\Controller;

class ApiController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['api manage'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
                'clientCollection' => 'apiServiceClientCollection',
            ],
        ];
    }

    public function successCallback($client)
    {
        ApiService::saveToken($client->id, $client->accessToken);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
}
