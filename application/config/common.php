<?php

$db = require(__DIR__ . '/db.php');
$params = require(__DIR__ . '/params.php');

return [
    'timeZone' => 'Europe/Moscow',
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
    ],
    'params' => $params,
];
