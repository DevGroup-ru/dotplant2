<?php

namespace app\modules\shop\components;


use app\modules\shop\models\Order;

interface SpecialPriceOrderInterface
{
    public function getPriceOrder(Order $order, $price);
}