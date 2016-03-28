<?php
namespace app\modules\shop\handlers;

use yii\base\Object;
use yii\web\UserEvent;
use app\modules\shop\models\Order;
use app\modules\shop\models\Wishlist;

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

    /**
     * Move wishlists/wishlist params from guest to logged/signed user
     * @param UserEvent $event
     */
    static public function moveWishlistsGuestToRegistered(UserEvent $event)
    {
        /** @var UserEvent $event */
        $wishlists = \Yii::$app->session->get('wishlists', []);
        foreach ($wishlists as $k => $id) {
            /** @var Wishlist $wishlist */
            $wishlist = Wishlist::findOne(['id' => $id]);
            if (null !== $wishlist && 0 === intval($wishlist->user_id)) {
                $wishlist->user_id = $event->identity->id;
                $wishlist->save();
            }
        }
    }
}
