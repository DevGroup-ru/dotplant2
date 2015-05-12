<?php

namespace app\widgets;

use app\modules\shop\models\Cart;
use yii\base\Widget;

class CartInfo extends Widget
{
    public $viewFile = 'cart-info';

    public function run()
    {
        $order = Cart::getCart(false);
        echo $this->render(
            $this->viewFile,
            [
                'order' => $order,
            ]
        );
    }
}
