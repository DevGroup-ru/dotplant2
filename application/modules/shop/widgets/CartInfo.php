<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\Order;
use yii\base\Widget;

class CartInfo extends Widget
{
    /**
     * @var string
     */
    public $viewFile = 'cart-info';

    public function run()
    {
        /** @var Order $order */
        $order = Order::getOrder(false);

        if (!empty($order)) {
            $order->calculate();
        }

        return $this->render(
            $this->viewFile,
            [
                'order' => $order,
            ]
        );
    }
}
