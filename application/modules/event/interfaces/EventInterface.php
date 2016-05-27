<?php

namespace app\modules\event\interfaces;


interface EventInterface
{
    /**
     * @return void
     */
    public static function attachEventsHandlers();
}