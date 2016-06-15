<?php

namespace app\modules\floatPanel\events;


use yii\base\Event;

/**
 * Class BeforeRenderEvent
 * @package app\modules\floatPanel\events
 *
 * @property \app\modules\floatPanel\models\MenuItem[] $items
 */
class BeforeRenderEvent extends Event
{
    public $items = [];
}