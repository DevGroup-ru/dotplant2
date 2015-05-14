<?php

namespace app\modules\shop\components;


use app\modules\shop\models\Discount;
use app\modules\shop\models\Order;
use app\modules\shop\models\Product;

interface DiscountInterface
{
    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null);
}