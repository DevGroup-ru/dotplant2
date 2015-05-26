<?php

namespace app\backend;

use app\backend\components\BackendController;
use app\backend\widgets\FloatingPanel;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\web\Application;
use yii\web\View;

/**
 * Base DotPlant2 Backend module handling core backend functions and floating panel
 * @package app\backend
 */
class BackendModule extends Module implements BootstrapInterface
{
    public $administratePermission = 'administrate';

    public $defaultRoute = 'dashboard/index';

    /**
     * @var array Configuration array for floating panel for content-managers
     */
    public $floatingPanel = [];

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->on(
            Application::EVENT_BEFORE_ACTION,
            function () use ($app) {
                if (
                    Yii::$app->requestedAction->controller->module instanceof BackendModule === false &&
                    Yii::$app->requestedAction->controller instanceof BackendController === false
                ) {
                    if (Yii::$app->user->can('administrate')) {
                        /*
                         * Apply floating panel only if requested action is not a part of backend
                         */
                        $app->getView()->on(
                            View::EVENT_BEGIN_BODY,
                            function () {
                                echo FloatingPanel::widget($this->floatingPanel);
                            }
                        );
                    }
                }
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/backend/views/configurable/_config',
                'configurableModel' => 'app\backend\models\ConfigConfigurationModel',
            ]
        ];
    }
}
