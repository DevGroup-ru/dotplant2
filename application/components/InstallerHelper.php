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
}