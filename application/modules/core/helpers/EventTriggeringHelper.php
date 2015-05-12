<?php

namespace app\modules\core\helpers;

use app\modules\core\assets\EventsAsset;
use app\modules\core\events\SpecialEvent;
use app\modules\core\models\EventHandlers;
use app\modules\core\models\Events;
use Yii;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * Class EventTriggeringHelper is a helper class for dealing with special application events.
 *
 * @package app\modules\core\helpers
 */
class EventTriggeringHelper
{
    /** Event is triggered by application */
    const TYPE_APPLICATION = 'application_trigger';

    /** Event is triggered by javascript as page loads */
    const TYPE_JS_IMMEDIATE = 'javascript_immediate';

    /** Event is triggered immediately as element appears is in viewport */
    const TYPE_JS_ON_REVEAL = 'javascript_reveal';

    /** Event is triggered if element appeared in viewport at least for 5 seconds */
    const TYPE_JS_ON_REVEAL_5SEC = 'javascript_reveal_5sec';

    /** Event is triggered if element appeared in viewport at least for 15 seconds */
    const TYPE_JS_ON_REVEAL_15SEC = 'javascript_reveal_15sec';

    /** Event is triggered if element appeared in viewport at least for 30 seconds */
    const TYPE_JS_ON_REVEAL_30SEC = 'javascript_reveal_30sec';

    /** Event is triggered manually by javascript - you should implement triggering yourself in your view's template */
    const TYPE_JS_MANUAL = 'javascript_manual';

    /**
     * Triggers special event.
     * If we are in app context(trigger called from app, not EventTriggerController):
     * - application-triggered handlers are binded to event, event is triggered immediately
     * - js-triggered handlers are added by JS
     *
     * If we are in EventTriggerController:
     * - TYPE_APPLICATION handlers ignored
     * - js handlers triggered!
     *
     *
     * @param SpecialEvent $event Event object with all corresponding data
     * @param bool $isAppContext True if event is triggered from application context, false if it is lazy call from js
     *
     * @return bool result of triggering event
     */
    public static function triggerSpecialEvent(SpecialEvent $event, $isAppContext = true)
    {
        /** @var Events $eventsModel Events model instance for determining handlers*/
        $eventsModel = Events::findByClassName($event->className());
        if ($eventsModel === null) {
            throw new Exception("No such event for this class ".$event->className());
        }

        foreach ($eventsModel->handlers as $handler) {
            /** @var EventHandlers $handler */
            if (
                ( $handler->triggering_type === self::TYPE_APPLICATION && $isAppContext === true ) ||
                ( $handler->triggering_type !== self::TYPE_APPLICATION && $isAppContext === false )
            ) {

                // Register this event

                $event->on(
                    $eventsModel->owner_class_name,
                    $eventsModel->event_name,
                    [$handler->handler_class_name, $handler->handler_function_name]
                );

            } elseif ($handler->triggering_type !== self::TYPE_APPLICATION) {
                // We are in app context and triggering type is not application
                // So we need to register JS triggers
                // add needed javascript code to view
                $jsData = Json::encode($event->eventData());

                $selector = (
                    empty($eventsModel->selectorPrefix)
                        ? ''
                        : $eventsModel->selectorPrefix . ' '
                    )
                    . $event->selector();

                $selector = Json::encode($selector);

                $triggeringType = Json::encode($handler->triggering_type);

                $eventName = Json::encode($eventsModel->event_name);

                Yii::$app->controller->view->registerJs(<<<JS
    DotPlant2Events.RegisterTrigger(
        $eventName,
        $triggeringType,
        $jsData,
        $selector
    );
JS
                );
                EventsAsset::register(Yii::$app->controller->view);
            }
        }

        // shoot event!
        $event->trigger(
            $eventsModel->owner_class_name,
            $eventsModel->event_name,
            $event
        );

        return true;
    }
}