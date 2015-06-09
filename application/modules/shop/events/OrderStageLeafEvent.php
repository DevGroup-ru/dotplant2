<?php

namespace app\modules\shop\events;

use app\modules\core\events\SpecialEvent;

class OrderStageLeafEvent extends SpecialEvent
{
    protected $status = false;

    /**
     * @return array Array of event data that will be passed though application or through js
     */
    public function eventData()
    {
        return [];
    }

    public function setStatus($status = false)
    {
        $this->status = boolval($status);
    }

    public function getStatus()
    {
        return $this->status;
    }
}
?>