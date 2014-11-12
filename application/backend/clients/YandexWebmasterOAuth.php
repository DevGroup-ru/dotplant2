<?php

namespace app\backend\clients;

use app\backend\models\ApiService;
use Yii;
use yii\authclient\OAuthToken;

class YandexWebmasterOAuth extends \yii\authclient\clients\YandexOAuth
{

    public $hostId;

    public function init()
    {
        parent::init();
        $this->apiBaseUrl = "https://webmaster.yandex.ru/api/v2/hosts/{$this->hostId}";
    }

    /**
     * Saves token in DB.
     * @param OAuthToken $token
     * @return ApiService|null|\yii\db\ActiveQuery|\yii\db\ActiveRecord|static
     */
    protected function saveAccessToken(OAuthToken $token)
    {
        return ApiService::saveToken($this->id, $token);
    }

    /**
     * Restores access token.
     * @return OAuthToken auth token.
     */
    protected function restoreAccessToken()
    {
        $token = ApiService::getToken($this->id);
        if (is_object($token)) {
            /* @var $token OAuthToken */
            if ($token->getIsExpired()) {
                $token = $this->refreshAccessToken($token);
            }
        }
        return $token;
    }
}
