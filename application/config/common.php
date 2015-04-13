<?php

Yii::setAlias('config', __DIR__);

$db = require(__DIR__ . '/db.php');
$params = require(__DIR__ . '/params.php');

return [
    'timeZone' => 'Europe/Moscow',
    'modules' => [
        'data' => [
            'class' => 'app\data\Module',
            'layout' => '@app/backend/views/layouts/main',
        ],
        'config' => [
            'class' => 'app\modules\config',
        ],
    ],
    'components' => [
        'db' => $db,
        'formatter' => [
            'class' => 'app\components\Formatter',
        ],
    ],
    'params' => $params,
];
