<?php

namespace app\modules\image\controllers;

use app\backgroundtasks\helpers\BackgroundTasks;
use app\components\SearchModel;
use app\modules\image\models\ErrorImage;
use Yii;
use yii\filters\AccessControl;

class BackendErrorImagesController extends \app\backend\components\BackendController
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
        $searchModel = new SearchModel(
            [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
                'model' => ErrorImage::className(),
                'scenario' => 'default',
            ]
        );
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionFind()
    {
        $result = BackgroundTasks::addTask(
            [
                'action' => 'images/check-broken',
                'name' => 'Find broken images',
                'description' => 'Find broken images',
                'init_event' => 'checkBrokenImages',
            ],
            ['create_notification' => false]
        );
        Yii::$app->session->setFlash(
            'info',
            Yii::t('app', $result ? 'Background task created' : 'Cannot create a task')
        );
        $this->redirect('index');
    }
}
