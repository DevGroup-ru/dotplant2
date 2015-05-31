<?php

namespace app\modules\shop\events;

use app\modules\core\events\SpecialEvent;

class OrderStageEvent extends SpecialEvent
{
    protected $eventData = [];

    /**
     * @return array Array of event data that will be passed though application or through js
     */
    public function eventData()
    {
        return $this->eventData;
    }

    public function setEventData(array $eventData)
    {
        $this->eventData = $eventData;
    }

    public function addEventData(array $eventData)
    {
        $this->eventData = array_merge($this->eventData, $eventData);
    }
}
?>