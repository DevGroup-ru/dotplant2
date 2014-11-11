<?php

use yii\helpers\ArrayHelper;

$db = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=dotplant2',
    'username' => 'root',
    'password' => 'rootpass',
    'charset' => 'utf8',
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];

return ArrayHelper::merge(
    $db,
    file_exists(__DIR__ . '/db-local.php') ? require(__DIR__ . '/db-local.php') : [],
    file_exists(__DIR__ . '/../web/theme/module/config/db.php')
    ? require(__DIR__ . '/../web/theme/module/config/db.php')
    : []
);
