<?php

$config = [
    'id' => 'dotplant2-installer',
    'basePath' => dirname(__DIR__),
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'language' => 'en',
    'defaultRoute' => 'installer/installer/index',
    'bootstrap' => [
        'installer',
    ],
    'modules' => [
        'installer' => [
            'class' => 'app\modules\installer\Module',
        ],
        'config' => [
            'class' => 'app\modules\config\ConfigModule',
        ],
        'core' => [
            'class' => 'app\modules\core\CoreModule',
        ],
        'DefaultTheme' => [
            'class' => 'app\extensions\DefaultTheme\Module',
        ],
        'user' => [
            'class' => 'app\modules\user\UserModule',
            'loginSessionDuration' => 2592000,
        ],
        'shop' => [
            'class' => 'app\modules\shop\ShopModule',
        ],
        'page' => [
            'class' => 'app\modules\page\PageModule',
        ],
        'backend' => [
            'class' => 'app\backend\BackendModule',
            'layout' => '@app/backend/views/layouts/main',
            'administratePermission' => 'administrate',
        ],
        'background' => [
            'class' => 'app\backgroundtasks\BackgroundTasksModule',
            'layout' => '@app/backend/views/layouts/main',
            'controllerNamespace' => 'app\backgroundtasks\controllers',
            'notifyPermissions' => ['task manage'],
            'manageRoles' => ['admin'],
        ],
        'seo' => [
            'class' => 'app\modules\seo\SeoModule',
            'include' => [
                'basic/default',
                'basic/page',
                'page/page',
                'shop/product',
                'shop/cart',
                'shop/product-compare',
            ],
        ],
        'review' => [
            'class' => 'app\modules\review\ReviewModule',
        ],
        'image' => [
            'class' => 'app\modules\image\ImageModule',
        ],
        'data' => [
            'class' => 'app\modules\data\DataModule',
        ],
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\DummyCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 6 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=dotplant2',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
        ],
        'request' => [
            'cookieValidationKey' => 'INSTALLER_COOKIE',
            'enableCsrfValidation' => false,
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'linkAssets' => YII_DEBUG && stripos(PHP_OS, 'win')!==0,
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource'
                ],
            ],
        ],

    ],
    'params' => [
        'icon-framework' => 'fa',
    ],
];

if (YII_CONSOLE) {
    echo "installer is running in console\n";
    unset($config['components']['request']);
    $config['defaultRoute'] = 'install/index';
    $config['controllerNamespace'] = 'app\modules\installer\commands';
    $config['components']['session'] = [
        'class' => 'app\modules\installer\components\StaticSession',
    ];
}

return $config;
