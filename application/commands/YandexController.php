<?php

namespace app\commands;

use app\backend\clients\YandexWebmasterOAuth;
use app\backgroundtasks\helpers\BackgroundTasks;
use yii\console\Controller;
use yii\console\Exception;

class YandexController extends Controller
{
    const SERVICE_NAME = 'yandexwebmaster';

    public function actionOriginalText($taskId)
    {
        $data = BackgroundTasks::getData($taskId, $this);
        if (isset($data['text'])) {
            /* @var $yandex YandexWebmasterOAuth */
            $yandex = \Yii::$app->apiServiceClientCollection->clients[self::SERVICE_NAME];
            print_r($yandex->api("original-texts", 'POST', [$data['text']]));
        } else {
            throw new Exception("No text");
        }
    }
}
