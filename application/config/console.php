<?php

use yii\helpers\ArrayHelper;

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');
Yii::setAlias('@webroot', dirname(__DIR__) . '/web');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [],
    'controllerNamespace' => 'app\commands',
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'modules' => [
        'background' => [
            'class' => 'app\backgroundtasks\BackgroundTasksModule',
            'controllerNamespace' => 'app\backgroundtasks\commands'
        ],
        'data' => [
            'class' => 'app\modules\data\DataModule',
            'controllerNamespace' => 'app\modules\data\commands'
        ],
        'seo' => [
            'class' => 'app\modules\seo\SeoModule',
            'mainPage' => '', // главная страница
        ],
        'shop' => [
            'class' => 'app\modules\shop\ShopModule',
            'controllerNamespace' => 'app\modules\shop\commands'
        ],
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                'tasks' => [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['background\*'],
                    'logFile' => '@runtime/logs/tasks.log',
                    'levels' => ['trace', 'error', 'warning', 'info'],
                ],
                'all' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'apiServiceClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                // имя клиента прописывается в Callback URI
                'yandexwebmaster' => [
                    'class' => 'app\backend\clients\YandexWebmasterOAuth',
                    'clientId' => '3ba7c6d1cc474483832bbfed8050a8e0',
                    'clientSecret' => '3a3b8b551b7e4c70b05274cf62688784',
                    'hostId' => '12341234',
                ],
            ],
        ],
        'fixture' => [
            'class' => 'yii\test\DbFixture',
            'basePath' => '@tests/unit/fixtures',
        ],
        'urlManager' => [
            'baseUrl' => '/',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'login/<service:google_oauth|facebook|etc>' => 'default/login',
                'login' => 'default/login',
                'logout' => 'default/logout',
                'signup' => 'default/signup',
                'cart/payment-result/<id:.+>' => 'cart/payment-result',
                'search' => 'default/search',
                'robots.txt' => 'seo/manage/get-robots',
                [
                    'class' => 'app\modules\page\components\PageRule',
                ],
                [
                    'class' => 'app\components\ObjectRule',
                ],
            ],
        ],
    ]
];

return ArrayHelper::merge(
    file_exists(__DIR__ . '/common.php') ? require(__DIR__ . '/common.php') : [],
    $config,
    file_exists(__DIR__ . '/../web/theme/module/config/common.php') ?
        require(__DIR__ . '/../web/theme/module/config/common.php') :
        [],

    file_exists(__DIR__ . '/common-configurables.php')
        ? require(__DIR__ . '/common-configurables.php')
        : [],

    file_exists(__DIR__ . '/../web/theme/module/config/console.php') ?
        require(__DIR__ . '/../web/theme/module/config/console.php') :
        [],

    file_exists(__DIR__ . '/console-configurables.php')
        ? require(__DIR__ . '/console-configurables.php')
        : [],
    file_exists(__DIR__ . '/common-local.php') ? require(__DIR__ . '/common-local.php') : [],
    file_exists(__DIR__ . '/console-local.php') ? require(__DIR__ . '/console-local.php') : []
);
