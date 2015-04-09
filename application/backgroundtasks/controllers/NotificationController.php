<?php

namespace app\backgroundtasks\controllers;

use app\backgroundtasks\models\NotifyMessage;
use app\backgroundtasks\models\Task;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class NotificationController
 * @package app\backgroundtasks\controllers
 * @author evgen-d <flynn068@gmail.com>
 */
class NotificationController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    /**
     * View all notifications
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->getModule('background')->inGroup()) {
            $searchModel = new NotifyMessage(['scenario' => 'search']);
            $dataProvider = $searchModel->search($_GET);
        } else {
            $searchModel = new NotifyMessage(['scenario' => 'search']);
            $dataProvider = $searchModel->search($_GET, true);
        }

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * View Notification model if exist
     * @param $id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        if (Yii::$app->getModule('background')->inGroup()) {
            /* @var $model NotifyMessage */
            $model = NotifyMessage::find()->joinWith(['task'])->where(
                [
                    'NotifyMessage.id' => $id,
                ]
            )->one();
        } else {
            /* @var $model NotifyMessage */
            $model = NotifyMessage::find()->joinWith(['task'])->where(
                [
                    NotifyMessage::tableName() . '.id' => $id,
                    Task::tableName() . '.initiator' => Yii::$app->user->id,
                ]
            )->one();
        }

        if ($model !== null) {
            switch ($model->result_status) {
                case NotifyMessage::STATUS_SUCCESS:
                    $class = 'panel-success';
                    break;
                case NotifyMessage::STATUS_FAULT:
                    $class = 'panel-danger';
                    break;
                default:
                    $class = 'panel-default';
                    break;
            }
            return $this->render(
                'view',
                [
                    'model' => $model,
                    'class' => $class,
                ]
            );
        } else {
            throw new NotFoundHttpException('repeated task #' . $id . ' not found');
        }
    }

    /**
     * Return count of new notifications
     * @param $current
     * @return mixed
     */
    public function actionOnlyNewNotifications($current)
    {
        if (Yii::$app->getModule('background')->inGroup()) {
            return NotifyMessage::find()->where('UNIX_TIMESTAMP(`ts`) > :current', [':current' => $current])->count();
        } else {
            return NotifyMessage::find()->where(
                [
                    'user_id' => Yii::$app->user->id,
                ]
            )->andWhere('UNIX_TIMESTAMP(`ts`) > :current', [':current' => $current])->count();
        }
    }

}
