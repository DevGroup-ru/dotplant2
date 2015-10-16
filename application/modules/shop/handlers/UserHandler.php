<?php
namespace app\modules\shop\handlers;

use yii\base\Object;
use yii\web\UserEvent;
use app\modules\shop\models\Order;

class UserHandler extends Object
{
    /**
     * Move orders/order params from guest to logged/signed user
     * @param UserEvent $event
     */
    static public function moveOrdersGuestToRegistered(UserEvent $event)
    {
        /** @var UserEvent $event */
        $orders = \Yii::$app->session->get('orders', []);
        foreach ($orders as $k => $id) {
            /** @var Order $order */
            $order = Order::findOne(['id' => $id]);
            if (null !== $order && 0 === intval($order->user_id)) {
                $order->user_id = $event->identity->id;
                $order->save();
            }
        }
    }
}
