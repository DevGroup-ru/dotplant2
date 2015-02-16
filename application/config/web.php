<?php

use yii\helpers\ArrayHelper;

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'language' => 'ru',
    'bootstrap' => [
        'seo',
        'backend',
        'app\components\UserPreferencesBootstrap',
    ],
    'defaultRoute' => 'default',
    'modules' => [
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
            'class' => 'app\seo\SeoModule',
            'include' => [
                'basic/default',
                'basic/page',
            ],
            'layout' => '@app/backend/views/layouts/main',
        ],
        'dynagrid' =>  [
            'class' => '\kartik\dynagrid\Module',
            'dbSettings' => [
                'tableName' => 'Dynagrid',
            ],
        ],
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
        ],
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'request' => [
            'enableCsrfValidation' => true,
            'cookieValidationKey' => 'njandsfkasbf',
        ],
        'response' => [
            'class' => 'app\components\Response',
        ],
        'urlManager' => [
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
                    'class' => 'app\components\PageRule',
                ],
                [
                    'class' => 'app\components\ObjectRule',
                ],
            ],
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,
                    'jsOptions' => [
                        'position' => \yii\web\View::POS_HEAD,
                    ],
                    'js' => [
                        '//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js',
                    ]
                ],
            ],
        ],
        'user' => [
            'class' => '\yii\web\User',
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/login'],
        ],
        'authManager' => [
            'class'=>'yii\\rbac\\DbManager',
            'cache' => 'cache',
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class' => 'yii\authclient\clients\GoogleOpenId'
                ],
                'yandex' => [
                    'class' => 'yii\authclient\clients\YandexOpenId'
                ],
                'facebook' => [
                    // register your app here: https://developers.facebook.com/apps/
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => '547268812035683',
                    'clientSecret' => '478d1f1024ee3b3c90cc976eb2ee6ff5',
                ],
                'vk' => [
                    'class' => '\app\components\VK',
                    'clientId' => '4119510',
                    'clientSecret' => 'UeyicFQWAhca5fKqPd0U',
                ],
            ],
        ],
        'apiServiceClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                // имя клиента прописывается в Callback URI
                'yandexwebmaster' => [
                    'class' => 'yii\authclient\clients\YandexOAuth',
                    'clientId' => '3ba7c6d1cc474483832bbfed8050a8e0',
                    'clientSecret' => '3a3b8b551b7e4c70b05274cf62688784',
                ],
            ],
        ],
        'errorHandler' => [
            'class' => 'app\components\DotplantErrorHandler',
            'errorAction' => 'default/error',
        ],
        'mail' => file_exists(__DIR__ . '/email-config.php') ? require(__DIR__ . '/email-config.php') : [ 'class' => 'yii\swiftmailer\Mailer' ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'filterquery' => [
            'class' => 'app\components\filters\FilterQueryChain',
            'filters' => [
                [
                    'class' => 'app\components\filters\ProductPriceRangeFilter',
                ]
            ]
        ],
        'session' => [
            'timeout' => 2592000, // 30 days
        ],
    ],
];

$allConfig = ArrayHelper::merge(
    file_exists(__DIR__ . '/common.php') ? require(__DIR__ . '/common.php') : [],
    $config,
    file_exists(__DIR__ . '/../web/theme/module/config/common.php')
    ? require(__DIR__ . '/../web/theme/module/config/common.php')
    : [],
    file_exists(__DIR__ . '/../web/theme/module/config/web.php')
    ? require(__DIR__ . '/../web/theme/module/config/web.php')
    : [],
    file_exists(__DIR__ . '/common-local.php') ? require(__DIR__ . '/common-local.php') : [],
    file_exists(__DIR__ . '/web-local.php') ? require(__DIR__ . '/web-local.php') : []
);

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $allConfig['bootstrap'][] = 'debug';
    $allConfig['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'panels' => [
            'holmes' => [
                'class' => 'app\panels\holmes\HolmesPanel',
            ],
        ],
    ];
    $allConfig['modules']['gii'] = 'yii\gii\Module';
}

return $allConfig;
