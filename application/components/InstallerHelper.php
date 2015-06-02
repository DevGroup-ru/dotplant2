<?php

namespace app\components;

use Yii;
use yii\helpers\ArrayHelper;

class InstallerHelper
{
    public static function checkPermissions()
    {
        $files = [
            '@app/config/db-local.php',
            '@app/config/web-local.php',
            '@app/config/common-configurables.php',
            '@app/config/console-configurables.php',
            '@app/config/web-configurables.php',
            '@app/config/kv-configurables.php',
            '@app/config/aliases.php',
        ];
        return array_reduce(
            $files,
            function($carry, $item) {
                $carry[$item] = is_writeable(Yii::getAlias($item));
                return $carry;
            },
            []
        );
    }

    public static function unlimitTime()
    {
        return set_time_limit(0);
    }

    public static function getLanguagesArray()
    {
        $yiiLanguages = [
            'ar',
            'az',
            'bg',
            'ca',
            'cs',
            'da',
            'de',
            'el',
            'es',
            'et',
            'fa',
            'fi',
            'fr',
            'he',
            'hu',
            'id',
            'it',
            'ja',
            'kk',
            'ko',
            'lt',
            'lv',
            'ms',
            'nl',
            'pl',
            'pt',
            'pt-BR',
            'ro',
            'ru',
            'sk',
            'sl',
            'sr',
            'sr-Latn',
            'sv',
            'th',
            'tj',
            'tr',
            'uk',
            'vi',
            'zh-CN',
            'zh-TW',

            // default!
            'en',
        ];
        $dotPlantLanguages = [
            'en',
            'ru',
            'zh-CN',
        ];
        $result = [];
        foreach ($yiiLanguages as $lang) {
            $result[] = [
                'language' => $lang,
                'translated' => in_array($lang, $dotPlantLanguages),
            ];
        }
        ArrayHelper::multisort($result, 'translated', SORT_DESC);
        return $result;
    }

    public static function askForUser()
    {
        $username = $email = $password = null;

        if (getenv("ADMIN_USERNAME")) {
            echo "INFO: Using admin user details provided by ENV variables...\n";
            $username = getenv("ADMIN_USERNAME");
            $email = getenv("ADMIN_EMAIL");
            $password = getenv("ADMIN_PASSWORD");

        } else {
            $stdIn = fopen("php://stdin", "r");
            do {
                echo 'Enter admin username (3 or more chars): ';
                $username = trim(fgets($stdIn));
            } while (mb_strlen($username) < 3);
            do {
                echo 'Enter admin email: ';
                $email = trim(fgets($stdIn));
            } while (preg_match('#^\w[\w\d\.\-_]*@[\w\d\.\-_]+\.\w{2,6}$#i', $email) != 1);
            do {
                do {
                    echo 'Enter admin password (8 or more chars): ';
                    $password = trim(fgets($stdIn));
                } while (mb_strlen($password) < 8);
                do {
                    echo 'Confirm admin password: ';
                    $confirmPassword = trim(fgets($stdIn));
                } while (mb_strlen($confirmPassword) < 8);
                if ($password != $confirmPassword) {
                    echo "Password does not match the confirm password\n";
                }
            } while ($password != $confirmPassword);
            fclose($stdIn);
        }
        if (getenv("SERVER_NAME")) {
            $serverName = getenv("SERVER_NAME");
        } else {
            $stdIn = fopen("php://stdin", "r");
            echo "\nEnter server name (ie. localhost): ";
            $serverName = trim(fgets($stdIn));
            if (empty($serverName)) {
                $serverName = 'localhost';
            }
            fclose($stdIn);
        }
//[$id, 'Server name', 'serverName', $serverName, 'core.serverName'],
//        $user = new User(['scenario' => 'signup']);
//        $user->username = $username;
//        $user->password = $password;
//        $user->email = $email;
//        $user->auth_key = '';
//        $user->save(false);
    }
    public static function makeUserAdmin($userId)
    {
//        $this->insert(
//            '{{%auth_assignment}}',
//            [
//                'item_name' => 'admin',
//                'user_id' => $user->id,
//            ]
//        );
    }
}