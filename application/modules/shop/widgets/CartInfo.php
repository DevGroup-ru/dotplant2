<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\Order;
use yii\base\Widget;

class CartInfo extends Widget
{
    public $viewFile = 'cart-info';

    public function run()
    {
        $order = Order::getOrder(false);
        echo $this->render(
            $this->viewFile,
            [
                'order' => $order,
            ]
        );
    }
}
