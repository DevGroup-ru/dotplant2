<?php

namespace app\backend\controllers;


use app\backgroundtasks\helpers\BackgroundTasks;
use app\models\Config;
use app\models\Image;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class ThumbnailController extends Controller
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

    public function actionRecreate($param)
    {
        $images = Image::find()->select('id');
        if ($param === 'config') {
            $ids = Config::getValue('images.IdsToRecreate', '');
            $images = $images->where(['id' => explode(',', $ids)]);
        }
        $param = ArrayHelper::getColumn($images->asArray()->all(), 'id');
        if (empty($param) === false) {
            BackgroundTasks::addTask(
                [
                    'action' => 'images/recreate-thumbnails',
                    'name' => 'Recreate images',
                    'description' => 'Creating YML file',
                    'params' => implode(',', $param),
                    'init_event' => 'yml',
                ],
                ['create_notification' => false]
            );
            Yii::$app->session->setFlash('info', Yii::t('app', 'Background task created'));
        }
        $this->redirect('index');
    }
}