<?php

namespace app\components;


use yii\helpers\ArrayHelper;

class InstallerHelper
{
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