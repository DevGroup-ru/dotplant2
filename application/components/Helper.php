<?php

namespace app\components;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Helper
{
    /**
     * Get all model records as array key => value.
     * @param string $className
     * @param string $keyAttribute
     * @param string $valueAttribute
     * @param bool $useCache
     * @return array
     */
    public static function getModelMap($className, $keyAttribute, $valueAttribute, $useCache = true)
    {
        /** @var ActiveRecord $className */
        $cacheKey = $className::tableName() . ': Map';
        $result = $useCache ? Yii::$app->cache->get($cacheKey) : false;
        if ($result === false) {
            $result = ArrayHelper::map($className::find()->all(), $keyAttribute, $valueAttribute);
            if ($useCache) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $result,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                \app\behaviors\TagDependency::getCommonTag($className),
                            ],
                        ]
                    )
                );
            }
        }
        return $result;
    }

    /**
     * Get time difference like "2 hours ago".
     * @param string $newDate
     * @param string $oldDate
     * @return string
     */
    public static function getTimeDifference($newDate, $oldDate)
    {
        $newDT = new \DateTime($newDate);
        $oldDT = new \DateTime($oldDate);
        $interval = $newDT->diff($oldDT);
        $ranges = [
            'y' => 'years',
            'm' => 'months',
            'd' => 'days',
            'h' => 'hours',
            'i' => 'minutes',
            's' => 'seconds',
        ];
        foreach ($ranges as $key => $range) {
            if ($interval->$key > 0) {
                return Yii::t('app', '{key} ' . $range . ' ego', ['key' => $interval->$key]);
            }
        }
        return Yii::t('app', 'Right now');
    }

    public static function createSlug($source)
    {
        $source = mb_strtolower($source, 'UTF-8');
        $translateArray = [
            "ый" => "y", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "c", "ч" => "ch" ,"ш" => "sh", "щ" => "sch", "ъ" => "",
            "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
            " " => "-", "." => "", "/" => "-"
        ];
        $source = preg_replace('#[^a-z0-9\-]#is', '', strtr($source, $translateArray));
        return trim(preg_replace('#-{2,}#is', '-', $source));
    }
}
