<?php

namespace app\modules\user\models;

use app;
use app\modules\config\models\BaseConfigurableModel;

/**
 * Class ConfigurableModel represents configuration model for retrieving user input in backend configuration subsystem.
 * @package app\modules\user\models
 */
class ConfigurableModel extends BaseConfigurableModel
{
    /**
     * Duration of login session for users in seconds.
     * By default 30 days.
     * @var int
     */
    public $loginSessionDuration = 2592000;

    /**
     * Expiration time in seconds for user password reset generated token.
     * @var int
     */
    public $passwordResetTokenExpire = 3600;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['passwordResetTokenExpire', 'loginSessionDuration',], 'number', 'integerOnly' => true, 'min' => 60],
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public function webApplicationAttributes()
    {
        return [
            'modules' => [
                'user' => [
                    'loginSessionDuration' => $this->loginSessionDuration,
                    'passwordResetTokenExpire' => $this->passwordResetTokenExpire,
                ],
            ],
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    public function consoleApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    public function commonApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public function keyValueAttributes()
    {
        return [];
    }
}