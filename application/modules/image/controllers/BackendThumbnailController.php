<?php

namespace app\modules\image\controllers;

use app\backgroundtasks\helpers\BackgroundTasks;
use Yii;
use yii\filters\AccessControl;

class BackendThumbnailController extends \app\backend\components\BackendController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['content manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', []);
    }

    public function actionRecreate()
    {
        BackgroundTasks::addTask(
            [
                'action' => 'images/recreate-thumbnails',
                'name' => 'Recreate images',
                'description' => 'Creating YML file',
                'params' => null,
                'init_event' => 'yml',
            ],
            ['create_notification' => false]
        );
        Yii::$app->session->setFlash('info', Yii::t('app', 'Background task created'));
        $this->redirect('index');
    }
}
