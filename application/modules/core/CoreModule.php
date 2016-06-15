<?php

namespace app\modules\core;

use app;
use app\backend\widgets\FloatingPanel;
use app\components\BaseModule;
use app\modules\core\models\ContentDecorator;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;

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
        'backend-wysiwyg' => 'app\modules\core\backend\WysiwygController',
    ];

    public $autoCompleteResultsCount = 5;

    public $fileUploadPath = '@webroot/upload/files/';
    public $visitorsFileUploadPath = '@app/visitors-uploaded/';
    public $removeUploadedFiles = true;
    public $overwriteUploadedFiles = false;

    public $spamCheckerApiKey;

    public $serverName = 'localhost';
    public $serverPort = 80;

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

    public $searchHandlers = [
        'query_search_products_by_description' => [
            'app\components\search\SearchProductsByDescriptionHandler'
        ],
        'query_search_products_by_property' => [
            'app\components\search\SearchProductsByPropertyHandler'
        ],
        'query_search_pages_by_description' => [
            'app\components\search\SearchPagesByDescriptionHandler'
        ]
    ];

    public $emailConfig = [
        'transport' => 'Swift_MailTransport',
        'host' => 'localhost',
        'username' => 'login',
        'password' => 'password',
        'port' => '25',
        'encryption' => '',
        'mailFrom' => 'login',
        'sendMail' => '',
    ];

    public $wysiwyg_id = 1;

    /** @var string|null Active WYSIWYG editor class name for use in backend forms */
    private $wysiwyg_class_name = '';

    /** @var array|null Active WYSIWYG editor params for use in backend forms */
    private $wysiwyg_params = null;

    /** @var bool Attach file properties to form email message */
    public $attachFilePropertiesToFormEmail = false;

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

    /**
     * @return string Active WYSIWYG widget class name for use in backend forms
     */
    public function wysiwyg_class_name()
    {
        if (empty($this->wysiwyg_class_name)) {
            $this->fetchWYSIWYG();
            if (class_exists($this->wysiwyg_class_name) === false) {
                $this->wysiwyg_id = 1;
                $this->fetchWYSIWYG();
            }
        }
        return $this->wysiwyg_class_name;
    }

    /**
     * @return array WYSIWYG params for widget
     */
    public function wysiwyg_params()
    {
        if ($this->wysiwyg_params === null) {
            $this->fetchWYSIWYG();
        }
        return $this->wysiwyg_params;
    }

    private function fetchWYSIWYG()
    {
        $data = app\modules\core\models\Wysiwyg::getClassNameAndParamsById($this->wysiwyg_id);
        if (!isset($data['class_name'])) {
            if ($this->wysiwyg_id > 1) {
                $this->wysiwyg_id = 1;
            } else {
                Yii::$app->cache->delete('WysiwygClassName:' . $this->wysiwyg_id);
            }
            return $this->fetchWYSIWYG();
        }
        $this->wysiwyg_class_name = $data['class_name'];
        $this->wysiwyg_params = $data['params'];
    }

    /**
     * @return string
     */
    public function getServerPort()
    {
        return $this->serverPort == 80 ? "" : ":{$this->serverPort}";
    }

    /**
     * Returns compiled baseUrl without schema servername:port
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->serverName . $this->getServerPort();
    }
}
