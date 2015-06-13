<?php

namespace app\modules\installer\components;

use app\modules\installer\models\AdminUser;
use app\modules\installer\models\FinalStep;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use app\modules\config\helpers\ConfigurationUpdater;
use app\modules\config\models\Configurable;

class InstallerHelper
{
    public static function checkPermissions()
    {
        $files = [
            '@app/config/db-local.php',
            '@app/config/web-local.php',
            '@app/config/common-local.php',
            '@app/config/common-configurables.php',
            '@app/config/console-configurables.php',
            '@app/config/web-configurables.php',
            '@app/config/kv-configurables.php',
            '@app/config/aliases.php',
        ];
        return array_reduce(
            $files,
            function($carry, $item) {
                $fn = Yii::getAlias($item);
                $result = touch($fn);
                if ($result) {
                    unlink($fn);
                }
                $carry[$item] = $result;
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
            'en-US',
        ];
        $dotPlantLanguages = [
            'en-US',
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

    public static function createDatabaseConfig($config)
    {
        $config['dsn'] = 'mysql:host='.$config['db_host'].';dbname='.$config['db_name'];
        $config['class'] = 'yii\db\Connection';
        unset($config['db_name'], $config['db_host'], $config['connectionOk']);
        return $config;
    }

    public static function createDatabaseConfigFile($config)
    {
        $content = "<?php\nreturn " . VarDumper::export($config) . ";\n";
        return file_put_contents(Yii::getAlias('@app/config/db-local.php'), $content) > 0;
    }

    public static function createAdminUser(AdminUser $model, \yii\db\Connection $db)
    {
        $db->createCommand()
            ->insert('{{%user}}', [
                'username' => $model->username,
                'password_hash' => Yii::$app->security->generatePasswordHash($model->password),
                'email' => $model->email,
                'auth_key' => '',
                'create_time' => time(),
                'update_time' => time(),
            ])
            ->execute();
        $userId = intval($db->lastInsertID);
        $assignmentResult = $db->createCommand()
            ->insert(
                '{{%auth_assignment}}',
                [
                    'item_name' => 'admin',
                    'user_id' => $userId,
                ]
            )
            ->execute() === 1;

        return ($assignmentResult && $userId > 0);
    }

    public static function writeCommonConfig(FinalStep $model)
    {
        $common_config = [
            'language' => Yii::$app->session->get('language'),
            'components' => [
                'cache' => [
                    'class' => $model->cacheClass,
                    'keyPrefix' => $model->keyPrefix,
                ],
            ],
            'modules' => [
                'core' => [
                    'serverName' => $model->serverName,
                ],
            ],
        ];
        if ($model->cacheClass === 'yii\caching\MemCache') {
            $common_config['components']['cache']['useMemcached'] = $model->useMemcached;
        }

        return file_put_contents(
            Yii::getAlias('@app/config/common-local.php'),
            "<?php\nreturn ". VarDumper::export($common_config).';'
        ) > 0;
    }

    public static function updateConfigurables()
    {
        Yii::$app->set('db', Yii::createObject(InstallerHelper::createDatabaseConfig(Yii::$app->session->get('db-config'))));
        Yii::$app->db->open();
        $conf = Configurable::find()->all();
        return ConfigurationUpdater::updateConfiguration($conf, false);
    }
}