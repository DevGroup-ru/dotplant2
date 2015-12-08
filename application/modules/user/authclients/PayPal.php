<?php

namespace app\modules\user\authclients;


use yii\authclient\OAuth2;

/**
 *
 * Write return_url in PayPal setting page http::/your-site.name/user/user/auth?authclient=PayPal&scope=openid
 *
 * Class PayPal
 * @package app\modules\user\authclients
 */
class PayPal extends OAuth2
{

    const URI_SANDBOX = "https://api.sandbox.paypal.com/v1/";
    const URI_LIVE = "https://api.paypal.com/v1/";
    const URI_AUTHORIZE_LIVE = 'https://www.paypal.com/';
    const URI_AUTHORIZE_SANDBOX = 'https://www.sandbox.paypal.com/';

    public $debug = false;

    public $scope = 'openid';

    public $authUrl = 'webapps/auth/protocol/openidconnect/v1/authorize';

    public $tokenUrl = 'identity/openidconnect/tokenservice';


    /**
     * Composes user authorization URL.
     * @param array $params additional auth GET params.
     * @return string authorization URL.
     */
    public function buildAuthUrl(array $params = [])
    {
        $defaultParams = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->getReturnUrl() . '&scope=openid',
            'response_type' => 'code',
        ];
        if (!empty($this->scope)) {
            $defaultParams['scope'] = $this->scope;
        }
        return $this->composeUrl($this->authUrl, array_merge($defaultParams, $params));
    }

    public function init()
    {
        if ($this->debug) {
            $this->tokenUrl = self::URI_SANDBOX . $this->tokenUrl;
            $this->authUrl = self::URI_AUTHORIZE_SANDBOX . $this->authUrl;
        } else {
            $this->tokenUrl = self::URI_LIVE . $this->tokenUrl;
            $this->authUrl = self::URI_AUTHORIZE_LIVE . $this->authUrl;
        }
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'paypal';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'PayPal';
    }


    /**
     * @inheritdoc
     */
    public function defaultViewOptions()
    {
        return [
            'popupWidth' => 1000,
            'popupHeight' => 600,
        ];
    }

    public function initUserAttributes()
    {
        $url = $this->debug ? self::URI_SANDBOX : self::URI_LIVE;
        return $this->api(
            $url . 'identity/openidconnect/userinfo/?schema=openid',
            'GET',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken->token,
                ]

            ]);
    }

}