<?php

namespace app\modules\core\helpers;

use app\modules\core\events\SpecialEvent;
use app\modules\core\models\Events;
use yii\base\Event;
use yii\base\Exception;

class EventSubscribingHelper
{
    public static function specialEventCallback($className, callable $callback)
    {
        /** @var Events $eventsModel Events model instance for determining handlers*/
        $eventsModel = Events::findByClassName($className);
        if ($eventsModel === null) {
            throw new Exception("No such event for this class ".$className);
        }

        Event::on(
            $eventsModel->owner_class_name,
            $eventsModel->event_name,
            $callback
        );
    }
}
