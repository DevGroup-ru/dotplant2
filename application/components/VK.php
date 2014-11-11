<?php

namespace app\components;

use yii\authclient\OAuth2;

class VK extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://oauth.vk.com/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://oauth.vk.com/access_token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.vk.com/method';
    /**
     * @inheritdoc
     */
    public $scope = 'email';

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        $data = $this->api('users.get', 'GET');
        if (!isset($data['response'][0])) {
            return null;
        }
        $data['response'][0]['id'] = $data['response'][0]['uid'];
        unset($data['response'][0]['uid']);
        return $data['response'][0];
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'vkontakte';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'VK';
    }
}
