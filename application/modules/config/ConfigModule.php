<?php

namespace app\modules\config;

use app;
use app\components\BaseModule;
use Yii;

/**
 * Base configuration module for DotPlant2 CMS
 * @package app\modules\config
 */
class ConfigModule extends BaseModule
{
    public $controllerMap = [
        'backend' => 'app\modules\config\backend\ConfigController',
    ];

    /**
     *
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerMap = [];
        }
    }
}
