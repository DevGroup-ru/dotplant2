#!/usr/bin/env php

<?php

define("SILENT_MODE", getenv("SILENT") == "1");

define("NEEDED_VERSION", '5.5.9');

if (version_compare(PHP_VERSION, NEEDED_VERSION) < 0) {
    die('ERROR: DotPlant2 needs at least PHP ' . NEEDED_VERSION . '. You are using ' . PHP_VERSION . "\n");
}

$f = fopen( 'php://stdin', 'r' );

$mysql = extension_loaded('pdo') && extension_loaded('pdo_mysql');

if ($mysql === false) {
    die("ERROR: DotPlant2 works only with PDO & MySQL\n");
}

$mcrypt = extension_loaded('mcrypt');

if ($mcrypt === false) {
    die("ERROR: DotPlant2 needs mcrypt extension to be installed");
}


$memcached_exists = extension_loaded('memcache') || extension_loaded('memcached');
$memcached_type = extension_loaded('memcached');

if ($memcached_exists === true) {

} else {
    echo "WARNING: We suggest you to use memcached for caching.\n";
    if (!SILENT_MODE) {
        echo "Continue using filecache instead? [y/N] ";
        while (true) {
            $answer = trim(fgets($f));

            if ($answer === 'y' || $answer === 'Y') {
                break;
            } elseif ($answer === 'n' || $answer === 'N') {
                die("\nINFO: User aborted.\n");
            }
            echo "Continue using filecache instead? [y/N] ";
        }
    }
}

// some chmods
system('chmod -R 777 runtime/');
system('chmod -R 777 web/assets/');
system('chmod -R 777 web/upload/');
system('chmod -R 777 web/data/');
system('chmod -R 777 messages/');
system('chmod +x yii');

$composer_status = null;
system('composer global require "fxp/composer-asset-plugin:1.0.0-beta3"');

system('composer install', $composer_status);
if ($composer_status != 0) {
    die("ERROR: Something wrong updating composer.\n");
}



include('requirements.php');

// предзагрузим yii
defined('YII_DEBUG') or define('YII_DEBUG', true);

// fcgi doesn't have STDIN defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');


// генерим локальный конфиг веб-а

$web_data = [
    'components' => [
        'request' => [
            'cookieValidationKey' => randomPassword(16),
        ],
    ],
];

if ($memcached_exists) {
    $web_data['components']['cache'] = [
        'class' => 'yii\caching\MemCache',
    ];
    if ($memcached_type === true) {
        $web_data['components']['cache']['useMemcached'] = true;
        $keyPrefix = getVariable('KEY_PREFIX', randomPassword());
        $web_data['components']['cache']['keyPrefix'] = $keyPrefix;
    }
} elseif (extension_loaded('apc')) {
    $keyPrefix = getVariable('KEY_PREFIX', randomPassword());

    $web_data['components']['cache'] = [
        'class' => 'yii\caching\ApcCache',
        'keyPrefix' => $keyPrefix
    ];
}

file_put_contents("config/web-local.php", "<?php\n return " . \yii\helpers\VarDumper::export($web_data) . ";");


// генерим конфиг базы, спросив у юзера данные или взяв их из ENV

$db_name = $db_user = $db_pass = $db_host = null;
if (getenv("DB_NAME")) {
    $db_name = getenv("DB_NAME");
    $db_user = getenv("DB_USER");
    $db_pass = getenv("DB_PASS");
    $db_host = getenv("DB_HOST") ? getenv("DB_HOST") : "localhost";
} else {
    echo "\nEnter Database host (ie. localhost): ";
    $db_host = trim(fgets($f));
    if (empty($db_host)) {
        $db_host = 'localhost';
    }

    echo "\nEnter Database user (ie. root): ";
    $db_user = trim(fgets($f));
    if (empty($db_user)) {
        $db_user = 'root';
    }

    echo "\nEnter Database user password: ";
    $db_pass = trim(fgets($f));

    echo "\nEnter Database name (ie. dotplant2): ";
    $db_name = trim(fgets($f));
    if (empty($db_name)) {
        $db_name = 'dotplant2';
    }
    echo "\n\n";
}

$db_config = [
    'dsn' => 'mysql:host='.$db_host.';dbname='.$db_name,
    'username' => $db_user,
    'password' => $db_pass,
];
file_put_contents("config/db-local.php", "<?php\n return " . \yii\helpers\VarDumper::export($db_config) . ";");

// все ENV будут автоматом переданы туда
passthru('yii migrate --interactive=0');



function randomPassword($num=14) {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789_";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $num; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function getVariable($envVar, $defaultValue) {
    if (($var = getenv($envVar)) === false) {
        echo "\nEnter {$envVar} (leave it blank to generate random value): ";
        $var = trim(fgets($f));
        if ($var === "") {
            $var = $defaultValue;
        }
    }
    echo "\nsetting cache {$envVar} to {$var}\n";

    return $var;
}