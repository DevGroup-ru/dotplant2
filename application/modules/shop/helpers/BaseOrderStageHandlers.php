<?php

namespace app\modules\shop\helpers;

use app\modules\shop\events\OrderStageEvent;
use app\modules\shop\events\OrderStageLeafEvent;

class BaseOrderStageHandlers
{
    /**
     * @param $event OrderStageLeafEvent
     */
    public static function handlePayment(OrderStageLeafEvent $event)
    {
        $event->setStatus(true);
    }

    /**
     * @param $event OrderStageLeafEvent
     */
    public static function handleDelivery(OrderStageLeafEvent $event)
    {
        $event->setStatus(true);
    }

    public static function handleStagePayment(OrderStageEvent $event)
    {
    }

    public static function handleStageDelivery(OrderStageEvent $event)
    {
    }
}
?>