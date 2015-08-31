<?php

namespace app\modules\shop\components;

/**
 * Interface OrderItemInterface that should implement every entity that can be countable OrderItem
 * @package app\modules\shop\components
 */
interface OrderItemInterface
{
    public function getPricePerPcs();

    public function canChangeQuantity();
    // to be done the rest

}