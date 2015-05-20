<?php

namespace app\modules\shop\components;


use app\modules\shop\models\Order;
use app\modules\shop\models\Product;

interface SpecialPriceProductInterface
{

    public function getPriceProduct(Product $product, Order $order = null, $price);

}