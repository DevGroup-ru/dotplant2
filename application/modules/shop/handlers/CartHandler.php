<?php
namespace app\modules\shop\handlers;

use app\modules\shop\events\CartActionEvent;
use app\modules\shop\ShopModule;
use yii\base\Object;
use yii\web\View;

class CartHandler extends Object
{
    static public function renderCartPreview(CartActionEvent $event)
    {
        $result = $event->getEventData();

        /** @var View $view */
        $view = \Yii::$app->getView();

        $order = $event->getOrder();
        $order->calculate(false, false);

        $result['itemModalPreview'] = $view->renderFile(
            ShopModule::getInstance()->getViewPath() . '/cart/item-modal-preview.php',
            ['order' => $order,]
        );

        $event->setEventData($result);
    }
}
