<?php

namespace app\modules\shop\controllers;

use app\modules\core\behaviors\DisableRobotIndexBehavior;
use app\modules\shop\models\Order;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Url;
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
            [
                'class' => DisableRobotIndexBehavior::className(),
                'setSameOrigin' => false
            ]
        ];
    }

    /**
     * @param null $hash
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionShow($hash = null)
    {
        $order = Order::findOne(['hash' => $hash]);
        if (empty($order)) {
            throw new NotFoundHttpException();
        }
        Url::remember('', '__shopCabinetUpdateGuestReturnUrl');
        return $this->render('show', ['order' => $order]);
    }

    /**
     * @return string
     */
    public function actionList()
    {
        $orders = [];
        $dataProvider = null;
        $currentOrderId = null;
        if (\Yii::$app->user->isGuest) {
            $orders = \Yii::$app->session->get('orders', []);
            $currentOrderId = \Yii::$app->session->get('orderId');
            $orders = Order::find()->where(['id' => $orders, 'user_id' => 0]);
        } else {
            $orders = Order::find()->where(['user_id' => \Yii::$app->user->id]);
            $currentOrderId = \Yii::$app->session->get('orderId');
        }
        if (!is_array($orders)) {
            $orders = $orders
                ->orderBy(['update_date'=>SORT_DESC]);

            $dataProvider = new ActiveDataProvider(
                [
                    'query' => $orders,
                    'pagination' => [
                        'pageSize' => 10,
                    ],
                ]
            );
        }

        return $this->render('list', [
            'orders' => $orders,
            'currentOrder' => $currentOrderId,
            'dataProvider' => $dataProvider,
        ]);
    }
}
?>