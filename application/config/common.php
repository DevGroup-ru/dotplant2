<?php

$db = require(__DIR__ . '/db.php');
$params = require(__DIR__ . '/params.php');

return [
    'timeZone' => 'Europe/Moscow',
    'modules' => [
        'data' => [
            'class' => 'app\data\Module',
            'layout' => '@app/backend/views/layouts/main',
        ],
        'index' => [
            'class' => 'app\index\Module',
        ],
    ],
    'components' => [
        'db' => $db,
        'i18n' => [
            'translations' => [
                'shop' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'fileMap' => [
                        'shop' => 'shop.php',
                    ],
                ],
            ],
        ],
        'index' => [
            'class' => 'app\index\IndexComponent',
            'storageComponent' => 'elasticsearch',
        ],
    ],
    'params' => $params,
];
