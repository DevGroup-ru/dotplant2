<?php

namespace app\backend;

use app\backend\components\BackendController;
use app\modules\floatPanel\widgets\FloatingPanel;
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
    const BACKEND_GRID_ONE_TO_ONE = 'one_to_one';
    const BACKEND_GRID_ONE_COLUMN = 'one_column';
    const BACKEND_GRID_ONE_TO_TWO = 'one_to_two';
    const BACKEND_GRID_TWO_TO_ONE = 'two_to_one';

    public $administratePermission = 'administrate';

    public $defaultRoute = 'dashboard/index';

    /**
     * @var array Configuration array for floating panel for content-managers
     */
    public $floatingPanel = [];

    public $wysiwygUploadDir = '/upload/images';

    public $backendEditGrids = [];

    public static function backendGridLabels()
    {
        return [
            self::BACKEND_GRID_ONE_COLUMN => Yii::t('app', 'One column'),
            self::BACKEND_GRID_ONE_TO_ONE => Yii::t('app', 'One to one'),
            self::BACKEND_GRID_ONE_TO_TWO => Yii::t('app', 'One to two'),
            self::BACKEND_GRID_TWO_TO_ONE => Yii::t('app', 'Two to one'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->on(
            Application::EVENT_BEFORE_ACTION,
            function () use ($app) {
                if (
                    Yii::$app->request->isAjax === false &&
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

    /**
     * Check if current request is being served by backend
     *
     * @return bool true - backend, false - frontend
     */
    public static function isBackend()
    {
        return Yii::$app->controller instanceof BackendController === true;
    }
}
