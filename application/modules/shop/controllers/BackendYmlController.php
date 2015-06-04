<?php

namespace app\modules\shop\controllers;

use app\backend\components\BackendController;
use app\components\Helper;
use app\modules\shop\models\Yml;
use app\backgroundtasks\helpers\BackgroundTasks;
use Yii;
use yii\filters\AccessControl;

class BackendYmlController extends BackendController
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

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render(
            'index',
            []
        );
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionSettings()
    {
        if (Yii::$app->request->isPost) {
            $model = new Yml();
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $model->saveConfig();
            } elseif ($model->hasErrors()) {
                Yii::$app->session->setFlash('error', Helper::formatModelErrors($model, '<br />'));
            }
            return $this->refresh();
        }

        $model = new Yml();
        $model->loadConfig();

        return $this->render(
            'settings',
            [
                'model' => $model
            ]
        );
    }

    /**
     * @return \yii\web\Response
     */
    public function actionCreate()
    {
        BackgroundTasks::addTask(
            [
                'name' => 'yml_generate',
                'description' => 'Creating YML file',
                'action' => 'shop/yml/generate',
                'params' => '',
                'init_event' => 'shop/yml',
            ],
            [
                'create_notification' => true,
            ]
        );

        Yii::$app->session->setFlash('success', 'Task has been created.');
        return $this->redirect(['settings']);
    }
}
