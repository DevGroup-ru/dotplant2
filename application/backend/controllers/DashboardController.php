<?php

namespace app\backend\controllers;

use app\backend\actions\FlushCacheAction;
use app\backend\models\Notification;
use app\components\Helper;
use vova07\imperavi\actions\GetAction;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['flush-cache'],
                        'roles' => ['cache manage'],
                    ],
                    [
                        'allow' => false,
                        'actions' => ['flush-cache'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['administrate'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        $uploadDir = Yii::$app->getModule('backend')->wysiwygUploadDir;
        return [
            'flush-cache' => [
                'class' => FlushCacheAction::className(),
            ],
            'imperavi-image-upload' => [
                'class' => 'vova07\imperavi\actions\UploadAction',
                'url' => $uploadDir,
                'path' => '@webroot' . $uploadDir,
            ],
            'imperavi-images-get' => [
                'class' => 'vova07\imperavi\actions\GetAction',
                'url' => $uploadDir,
                'path' => '@webroot' . $uploadDir,
                'type' => GetAction::TYPE_IMAGES,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionNotifications($id = null)
    {
        $pageSize = 10;
        $query = Notification::find()->where(
            [
                'user_id' => \Yii::$app->user->id,
            ]
        )->orderBy('`id` DESC');
        $showMoreLink = true;
        if (is_null($id)) {
            $query->andWhere(['viewed' => 0]);
        } else {
            $query->andWhere(['viewed' => 1]);
            if ($id != -1) {
                $query->andWhere(['<', 'id', $id]);
            }
            $query->limit($pageSize);
        }
        $notifications = $query->all();
        $count = count($notifications);
        if (!is_null($id)) {
            if ($count < $pageSize) {
                $showMoreLink = false;
            }
            if ($count > 0) {
                $id = $notifications[$count - 1]->id;
            }
        } else {
            $id = -1;
        }
        return $this->renderPartial(
            'notifications',
            [
                'id' => $id,
                'notifications' => $notifications,
                'showMoreLink' => $showMoreLink,
            ]
        );
    }

    public function actionMarkNotification($id)
    {
        $notification = Notification::findOne($id);
        if (is_null($notification) || $notification->user_id != \Yii::$app->user->id) {
            throw new NotFoundHttpException;
        }
        $notification->viewed = 1;
        if (!$notification->save(true, ['viewed'])) {
            throw new ServerErrorHttpException;
        }
        return 'Notification has been marked as viewed';
    }

    public function actionMakeSlug($word)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return Helper::createSlug($word);
    }
}
