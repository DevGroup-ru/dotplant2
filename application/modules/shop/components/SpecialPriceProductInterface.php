<?php

namespace app\modules\shop\components;


use app\modules\shop\models\Order;
use app\modules\shop\models\Product;

interface SpecialPriceProductInterface
{

    public function setPriceProduct(Product &$product, Order $order = null);
}