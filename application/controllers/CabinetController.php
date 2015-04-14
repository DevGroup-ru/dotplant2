<?php

namespace app\controllers;

use app\models\Order;
use app\models\Property;
use app\models\PropertyGroup;
use app\modules\user\models\User;
use app\properties\HasProperties;
use Yii;
use yii\base\Security;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CabinetController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['order'],
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

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionOrder($id)
    {
        $order = Order::findOne(['hash' => $id]);
        if (is_null($order)) {
            throw new NotFoundHttpException;
        }
        return $this->render('order', ['order' => $order]);
    }

    public function actionOrders()
    {
        $query = Order::find();
        $needToSearch = true;
        if (!Yii::$app->user->isGuest) {
            $query->AndWhere(['user_id' => Yii::$app->user->id]);
        } else {
            if (Yii::$app->session->has('orders')) {
                $query->andWhere(['id', 'in', Yii::$app->session->get('orders')]);
            } else {
                $needToSearch = false;
            }
        }
        if ($needToSearch) {
            $orders = $query->all();
        } else {
            $orders = [];
        }
        $canceledOrders = [];
        $currentOrders = [];
        $doneOrders = [];
        foreach ($orders as $order) {
            switch ($order->order_status_id) {
                case Order::STATUS_DONE:
                    $doneOrders[] = $order;
                    break;
                case Order::STATUS_CANCEL:
                    $canceledOrders[] = $order;
                    break;
                default:
                    $currentOrders[] = $order;
            }
        }
        unset($orders);
        return $this->render(
            'orders',
            [
                'canceledOrders' => $canceledOrders,
                'currentOrders' => $currentOrders,
                'doneOrders' => $doneOrders,
            ]
        );
    }


}
