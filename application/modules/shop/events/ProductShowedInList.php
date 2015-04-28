<?php

namespace app\modules\shop\events;

use app\modules\core\events\SpecialEvent;

class ProductShowedInList extends SpecialEvent
{
    public $product_id = null;

    public function eventData()
    {
        return['product_id' => $this->product_id];
    }
}