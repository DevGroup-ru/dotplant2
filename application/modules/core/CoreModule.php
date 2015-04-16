<?php

namespace app\modules\core;

use app;
use app\components\BaseModule;
use app\components\Controller;
use app\modules\core\models\ContentDecorator;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
/**
 * Core module handles base DotPlant2 functions
 * @package app\modules\user
 */
class CoreModule extends BaseModule implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
//            'configurableModule' => [
//                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
//                'configurationView' => '@app/modules/user/views/configurable/_config',
//                'configurableModel' => 'app\modules\user\models\ConfigConfigurableModel',
//            ]
        ];
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\web\Application === true) {
            $app->on(
                Application::EVENT_BEFORE_ACTION,
                function () use ($app) {
                    $controller = Yii::$app->requestedAction->controller;

                    $decorators = ContentDecorator::getAllDecorators();
                    foreach ($decorators as $decorator) {
                        $decorator->subscribe($app, $controller);
                    }


                }
            );
        }
    }
}