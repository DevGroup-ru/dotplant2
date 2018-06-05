<?php
namespace app\modules\shop\handlers;

use app\modules\shop\controllers\CartController;
use app\modules\shop\events\CartActionEvent;
use app\modules\shop\models\Order;
use app\modules\shop\ShopModule;
use yii\base\BaseObject;
use yii\web\View;

class CartHandler extends BaseObject
{
    /**
     * @param CartActionEvent $event
     */
    static public function renderCartPreview(CartActionEvent $event)
    {
        $result = $event->getEventData();

        /** @var View $view */
        $view = \Yii::$app->getView();

        $order = $event->getOrder();
        $order->calculate(false, false);

        $result['itemModalPreview'] = $view->renderFile(
            ShopModule::getInstance()->getViewPath() . '/cart/partial/item-modal-preview.php',
            ['order' => $order,]
        );

        /** Backward compatibility */
        if (true === in_array($event->name, [CartController::EVENT_ACTION_ADD, CartController::EVENT_ACTION_REMOVE])) {
            $result['bcItemModalPreview'] = self::bcRenderCartPreview($order, $view, $event->getProducts());
        }
        /** Backward compatibility [END] */

        $event->setEventData($result);
    }

    /**
     * Backward compatibility
     * @param Order $order
     * @param View $view
     * @param array $products
     * @return string
     */
    static private function bcRenderCartPreview(Order $order, View $view, $products = [])
    {
        return array_reduce($products, function($result, $item) use ($order, $view) {
            return $result .= $view->renderFile(
                ShopModule::getInstance()->getViewPath() . '/cart/item-modal-preview.php',
                [
                    'order' => $order,
                    'orderItem' => $item['orderItem'],
                    'product' => $item['model'],
                ]
            );
        }, '');
    }
}
