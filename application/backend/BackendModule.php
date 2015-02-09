<?php

namespace app\backend;

use app\backend\widgets\FloatingPanel;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\web\Application;
use yii\web\View;

class BackendModule extends Module implements BootstrapInterface
{
    public $administratePermission = 'administrate';

    public $defaultRoute = 'dashboard/index';

    /**
     * @var array Configuration array for floating panel for content-managers
     */
    public $floatingPanel = [];


    public function bootstrap($app)
    {
        $app->on(
            Application::EVENT_BEFORE_ACTION,
            function () use ($app) {
                if ($app->requestedAction->controller->module->className() !== BackendModule::className() && Yii::$app->user->isGuest === false) {
                    if (Yii::$app->user->can('administrate')) {
                        $app->getView()->on(View::EVENT_BEGIN_BODY, function () {

                            echo FloatingPanel::widget($this->floatingPanel);

                        });
                    }
                }
            }
        );
    }
}
