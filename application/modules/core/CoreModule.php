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
    // To be implemented!
    public $themeExtensionId = 0;

    public $themeModuleName = '';

    public $themeModuleLocation = '';

    public $controllerMap = [
        'backend-extensions' => 'app\modules\core\backend\ExtensionsController',
        'backend-routing' => 'app\modules\core\backend\RoutingController',
    ];

    public $autoCompleteResultsCount = 5;

    public $fileUploadPath = 'upload/user-uploads/';

    public $spamCheckerApiKey;

    public $serverName = 'localhost';

    public $daysToStoreSubmissions = 28;

    /**
     * @var string Internal encoding. It's used for mbstring functions.
     */
    public $internalEncoding = 'UTF-8';

    public $errorMonitorEnabled = false;
    public $emailNotifyEnabled = false;
    public $devmail = '';
    public $notifyOnlyHttpCodes = '';
    public $numberElementsToStore = 5;
    public $immediateNotice = false;
    public $immediateNoticeLimitPerUrl = 10;
    public $httpCodesForImmediateNotify = '404,500';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/core/views/configurable/_config',
                'configurableModel' => 'app\modules\core\models\ConfigConfigurationModel',
            ]
        ];
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        mb_internal_encoding($this->internalEncoding);
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

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerMap = [];
        }
    }
}
