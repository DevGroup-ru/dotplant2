<?php

Yii::setAlias('config', __DIR__);

$db = require(__DIR__ . '/db.php');
$params = require(__DIR__ . '/params.php');

if (file_exists(__DIR__ . '/aliases.php')) {
    $aliases = require(__DIR__ . '/aliases.php');
    foreach ($aliases as $alias => $value) {
        Yii::setAlias($alias, $value);
    }
}

return [
    'timeZone' => 'Europe/Moscow',
    'bootstrap' => [
        'mail',
        'event'
    ],
    'modules' => [
        'data' => [
            'class' => 'app\data\Module',
            'layout' => '@app/backend/views/layouts/main',
        ],
        'config' => [
            'class' => 'app\modules\config\ConfigModule',
        ],
        'core' => [
            'class' => 'app\modules\core\CoreModule',
        ],
        'image' => [
            'class' => 'app\modules\image\ImageModule',
        ],
        'event' => [
            'class' => 'app\modules\event\EventModule'
        ]
    ],
    'components' => [
        'db' => $db,
        'formatter' => [
            'class' => 'app\components\Formatter',
        ],
        'updateHelper' => [
            'class' => 'app\modules\core\helpers\UpdateHelper',
        ],
        'mail' => [
            'class' => '\app\modules\core\components\MailComponent',
        ],
    ],
    'params' => $params,
    'controllerMap' => [
        'stubs' => [
            'class' => 'bazilio\stubsgenerator\StubsController',
        ],
    ],
];
