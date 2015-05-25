<?php

namespace app\modules\shop\events;

use app\modules\core\events\SpecialEvent;

class OrderCalculateEvent extends SpecialEvent
{
    public $order = null;
    public $price = null;

    public function eventData()
    {
        return [];
    }

}