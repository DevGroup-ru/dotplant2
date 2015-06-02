<?php

namespace app\modules\shop\events;

use app\modules\core\events\SpecialEvent;
use app\modules\shop\models\Order;

/**
 * Class OrderCalculateEvent
 * @package app\modules\shop\events
 * @property float $price
 * @property Order $order
 */
class OrderCalculateEvent extends SpecialEvent
{
    const BEFORE_CALCULATE = 0;
    const AFTER_CALCULATE = 0;

    public $state = OrderCalculateEvent::BEFORE_CALCULATE;
    public $order = null;
    public $price = null;

    public function eventData()
    {
        return [];
    }
}
