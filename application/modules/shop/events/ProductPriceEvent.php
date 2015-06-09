<?php

namespace app\modules\shop\events;


use app\modules\core\events\SpecialEvent;

class ProductPriceEvent extends SpecialEvent
{

    public $product = null;
    public $order = null;
    public $price;

    public function eventData()
    {
        return [];
    }

}