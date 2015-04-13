<?php

namespace app\modules\user;

use app;
use app\components\BaseModule;

/**
 * User module is the base core module of DotPlant2 CMS handling all user-related actions
 * @package app\modules\user
 */
class UserModule extends BaseModule
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
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/user/views/configurable/_config',
                'configurableModel' => 'app\modules\user\models\ConfigurableModel',
            ]
        ];
    }
}