<?php

namespace app\modules\user\authclients;

use Yii;

/**
 * Class VKontakte is personalized version of base yii2 vkontakte auth client
 * @package app\modules\user\authclients
 */
class VKontakte extends \yii\authclient\clients\VKontakte {
    /**
     * @inheritdoc
     * This method adds lang param to each api call
     */
    public function apiInternal($accessToken, $url, $method, array $params, array $headers) {
        $params['lang'] = Yii::$app->language === 'ru' ? 'ru' : 'en';
        return \yii\authclient\clients\VKontakte::apiInternal($accessToken, $url, $method, $params, $headers);
    }
}
