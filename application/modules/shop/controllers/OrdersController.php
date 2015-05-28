<?php

namespace app\modules\shop\controllers;

use app\modules\shop\models\Order;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class OrdersController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['show', 'list'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function actionShow($hash = null)
    {
        $order = Order::findOne(['hash' => $hash]);
        if (empty($order)) {
            throw new NotFoundHttpException();
        }
        return $this->render('show', ['order' => $order]);
    }

    public function actionList()
    {
        $orders = [];
        $currentOrderId = null;
        if (\Yii::$app->user->isGuest) {
            $orders = \Yii::$app->session->get('orders', []);
            $currentOrderId = \Yii::$app->session->get('orderId');
            $orders = Order::find()->where(['id' => $orders, 'user_id' => 0])->all();
        } else {
            $orders = Order::find()->where(['user_id' => \Yii::$app->user->id])->all();
            $currentOrderId = \Yii::$app->session->get('orderId');
        }

        return $this->render('list', [
            'orders' => $orders,
            'currentOrder' => $currentOrderId,
        ]);
    }
}
?>