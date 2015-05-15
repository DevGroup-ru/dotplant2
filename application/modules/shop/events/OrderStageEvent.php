<?php

namespace app\modules\shop\events;

use app\modules\core\events\SpecialEvent;

class OrderStageEvent extends SpecialEvent
{
    /**
     * @return array Array of event data that will be passed though application or through js
     */
    public function eventData()
    {
        return [];
    }
}
?>