<?php

namespace app\components;

use app;
use app\modules\user\models\UserService;
use yii\base\ErrorException;

/**
 * AuthClientHelper is a helper class for serving login through social networks and retrieving needed information by api
 * @package app\components
 */
class AuthClientHelper
{
    public static $ServiceIdMapping = [
        'app\modules\user\authclients\GitHub' => 'id',
        'yii\authclient\clients\YandexOpenId' => 'id',
        'yii\authclient\clients\Twitter' => 'id',
        'app\modules\user\authclients\Facebook' => 'id',
        'app\modules\user\authclients\VKontakte' => 'uid',
        'yii\authclient\clients\YandexOAuth' => 'id',
        'yii\authclient\clients\GoogleOAuth' => 'id',
        'app\modules\user\authclients\PayPal' => 'user_id'
    ];

    /**
     * Finds service record for current logged client and returns corresponding user.
     * @param \yii\authclient\BaseClient $client AuthClient instance with social authenticated details(ie. user attributes)
     * @throws ErrorException
     * @return app\modules\user\models\User|null
     */
    public static function findUserByService(\yii\authclient\BaseClient $client)
    {
        $serviceType = $client->className();
        if (isset(static::$ServiceIdMapping[$client->className()])) {
            $id_attribute = static::$ServiceIdMapping[$client->className()];
            $attributes = $client->getUserAttributes();
            $serviceId = null;
            if (isset($attributes[$id_attribute])) {
                $serviceId = $attributes[$id_attribute];
            } else {
                throw new ErrorException("No user identified supplied by social service.");
            }
            /** @var \app\modules\user\models\UserService $service */
            $service = UserService::find()
                ->where([
                    'service_type' => $serviceType,
                    'service_id' => $serviceId,
                ])
                ->with('user')
                ->one();

            if ($service === null) {
                return null;
            }

            return $service->user;
        } else {
            throw new ErrorException("Unidentified social service used.");
        }

    }

    /**
     * Retrieves additional profile information which can be needed for first-login(registration)
     * and which was not provided by first api call.
     * Returns merged user attributes
     * @param \yii\authclient\BaseClient $client
     * @return \yii\authclient\BaseClient Client with merged attributes
     */
    public static function retrieveAdditionalData(\yii\authclient\BaseClient $client)
    {
        $attributes = $client->getUserAttributes();

        switch ($client->className()) {
            case 'app\modules\user\authclients\GitHub':
                try {
                    /** @var \app\modules\user\authclients\GitHub $client */
                    $emails = $client->api('user/emails');

                    foreach ($emails as $email) {
                        if ($email['primary'] === true) {
                            $attributes['email'] = $email['email'];
                            break;
                        }
                    }

                } catch (\yii\authclient\InvalidResponseException $e) {
                    // no email :-(
                }
                break;
            default:
                break;
        }
        $client->setUserAttributes($attributes);
        return $client;
    }


    /**
     * Converts service attributes to app\modules\user\models\User model attributes
     * @param \yii\authclient\BaseClient $client
     * @return array Array of attributes by model type which we can apply by $model->setAttributes()
     */
    public static function mapUserAttributesWithService(\yii\authclient\BaseClient $client)
    {
        $mappings = [
            'service' => [
                // id of user in service
                'service_id' => static::$ServiceIdMapping,
            ],
            'user' => [
                'username' => [
                    'app\modules\user\authclients\GitHub' => 'login',
                    'yii\authclient\clients\Twitter' => 'screen_name',
                    'app\modules\user\authclients\VKontakte' => 'nickname',
                    'yii\authclient\clients\YandexOAuth' => 'login',
                ],
                'email' => [
                    'app\modules\user\authclients\GitHub' => 'email',
                    'yii\authclient\clients\YandexOpenId' => 'email',
                    'app\modules\user\authclients\Facebook' => 'email',
                    'yii\authclient\clients\YandexOAuth' => 'default_email',
                ],
                'first_name' => [
                    'app\modules\user\authclients\Facebook' => 'first_name',
                    'app\modules\user\authclients\VKontakte' => 'first_name',
                    'yii\authclient\clients\YandexOAuth' => 'first_name',
                ],
                'last_name' => [
                    'app\modules\user\authclients\Facebook' => 'last_name',
                    'app\modules\user\authclients\VKontakte' => 'last_name',
                    'yii\authclient\clients\YandexOAuth' => 'last_name',
                ],
                'avatar_url' => [
                    'app\modules\user\authclients\GitHub' => 'avatar_url',
                    'yii\authclient\clients\Twitter' => 'profile_image_url',
                    'app\modules\user\authclients\VKontakte' => 'photo',
                ],
                'company' => [
                    'app\modules\user\authclients\GitHub' => 'company',
                ],
                'url' => [
                    'app\modules\user\authclients\GitHub' => 'html_url',
                ],
                'location' => [
                    'app\modules\user\authclients\GitHub' => 'location',
                ],
            ],
        ];

        $class_name = $client->className();
        $attributes = $client->getUserAttributes();
        $result = [];
        foreach ($mappings as $model_type => $mappings_by_attribute) {
            $result [$model_type] = [];

            foreach ($mappings_by_attribute as $attribute => $maps) {
                if (isset($maps[$class_name])) {
                    $key_in_attributes = $maps[$class_name];
                    $value = null;
                    if (is_array($key_in_attributes)) {
                        $value = [];
                        foreach ($key_in_attributes as $key) {
                            if (isset($attributes[$key])) {
                                $value[] = $attributes[$key];
                            }
                        }
                        if (count($value) > 0) {
                            $value = implode(' ', $value);
                        } else {
                            $value = null;
                        }
                    } else {
                        $value = isset($attributes[$key_in_attributes]) ? $attributes[$key_in_attributes] : null;
                    }

                    if ($value !== null) {
                        $result[$model_type][$attribute] = $value;
                    }
                }
            }
        }

        return $result;
    }

}