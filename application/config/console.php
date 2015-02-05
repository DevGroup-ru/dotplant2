<?php

use yii\helpers\ArrayHelper;
use app\models\Config;

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
            'class' => 'app\data\Module',
            'controllerNamespace' => 'app\data\commands'
        ],
        'seo' => [
            'class' => 'app\seo\SeoModule',
            'mainPage' => '', // главная страница
        ],
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
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
            'class' => 'yii\test\DbFixtureManager',
            'basePath' => '@tests/unit/fixtures',
        ],
    ]
];

return ArrayHelper::merge(
    file_exists(__DIR__ . '/common.php') ? require(__DIR__ . '/common.php') : [],
    $config,
    file_exists(__DIR__ . '/../web/theme/module/config/common.php') ?
    require(__DIR__ . '/../web/theme/module/config/common.php') :
    [],
    file_exists(__DIR__ . '/../web/theme/module/config/console.php') ?
    require(__DIR__ . '/../web/theme/module/config/console.php') :
    [],
    file_exists(__DIR__ . '/common-local.php') ? require(__DIR__ . '/common-local.php') : [],
    file_exists(__DIR__ . '/console-local.php') ? require(__DIR__ . '/console-local.php') : [],
    file_exists(__DIR__ . '/from-db.php') ? require(__DIR__ . '/from-db.php') : []
);
