<?php

namespace app\modules;

use Yii;
use yii\base\Module;
use yii\base\BootstrapInterface;
use yii\base\Application;

class ThemeModule extends Module implements BootstrapInterface
{
    public function bootstrap($app)
    {
        // prevent bootstraping in console application
        if ($app instanceof \yii\console\Application) {
            return;
        }
    }
}
