<?php

namespace app\backend\models;

use yii\authclient\OAuthToken;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "api_service".
 *
 * @property string $service_id
 * @property string $access_token
 * @property string $token_type
 * @property integer $expires_in
 * @property integer $create_ts
 */
class ApiService extends ActiveRecord
{
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return '{{%api_service}}';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['service_id', 'access_token', 'token_type', 'expires_in', 'create_ts'], 'required'],
            [['expires_in', 'create_ts'], 'integer'],
            [['service_id', 'access_token', 'token_type'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'service_id' => 'Service ID',
            'access_token' => 'Access Token',
            'token_type' => 'Token Type',
            'expires_in' => 'Expires In',
            'create_ts' => 'Create Timestamp',
        ];
    }

    /**
     * @param $name
     * @param $token
     * @return ApiService|null|\yii\db\ActiveQuery|\yii\db\ActiveRecord
     */
    public static function saveToken($name, OAuthToken $token)
    {
        $service = self::findOne($name);
        if ($service === null) {
            $service = new ApiService(
                [
                    'service_id' => $name,
                ]
            );
        }
        $service->access_token = $token->params['access_token'];
        $service->token_type = $token->params['token_type'];
        $service->expires_in = $token->params['expires_in'];
        $service->create_ts = $token->createTimestamp;
        return $service->save();
    }

    public static function getToken($name)
    {
        $service = self::findOne($name);
        if ($service !== null) {
            $token = new OAuthToken(
                [
                    'tokenParamKey' => 'access_token',
                    'createTimestamp' => $service->create_ts,
                    'params' => [
                        'access_token' => $service->access_token,
                        'token_type' => $service->token_type,
                        'expires_in' => $service->expires_in,
                    ],
                ]
            );
        } else {
            $token = null;
        }
        return $token;
    }
}
