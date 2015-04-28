<?php

namespace app\modules\core\controllers;

use app;
use app\components\Controller;
use app\modules\core\helpers\EventTriggeringHelper;
use app\modules\core\models\Events;
use Yii;
use yii\helpers\Json;

class EventsBeaconController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => app\behaviors\Csrf::className(),
                'disabledActions' => [
                    'index',
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $data = Json::decode(Yii::$app->request->rawBody);
        if (is_array($data) === true) {

            // sort by event name
            $eventsNames = array_keys(
                array_reduce(
                    $data,
                    function($carry, $item) {
                        if (isset($item['eventName'])) {
                            $carry[$item['eventName']] = 1;
                        }
                        return $carry;
                    },
                    []
                )
            );

            // preload all events into identity map
            Events::findByNames($eventsNames);

            // now we can handle it all
            foreach ($data as $eventItem) {
                if (isset($eventItem['eventName'], $eventItem['event'], $eventItem['timestamp']) === true) {
                    $eventModel = Events::findByName($eventItem['eventName']);
                    if ($eventModel !== null) {
                        $className = $eventModel->event_class_name;
                        $specialEvent = new $className($eventItem['event']);
                        EventTriggeringHelper::triggerSpecialEvent($specialEvent);
                    }
                }
            }
        }
    }
}