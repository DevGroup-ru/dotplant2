<?php

namespace app\seo\models;

use yii\caching\TagDependency;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
/**
 * Class Robots
 * @package app\seo\models
 * @property string $key = self::KEY_ROBOTS
 * @property string $value
 */
class Robots extends Config
{
    const KEY_ROBOTS = 'robots.txt';

    public function init()
    {
        parent::init();
        $this->key = self::KEY_ROBOTS;
    }

    public static function getRobots()
    {
        /* @var $robots \app\seo\models\Config|null */

        $cacheKey = Yii::$app->getModule('seo')->cacheConfig['robotsCache']['name'];

        $robots = Yii::$app->getCache()->get($cacheKey);
        if (!$robots) {
            $robots = self::findOne(self::KEY_ROBOTS);
            Yii::$app->getCache()->set(
                $cacheKey,
                $robots,
                Yii::$app->getModule('seo')->cacheConfig['robotsCache']['expire'],
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(Config::className()),
                    ]
                ])
            );
        }
        if ($robots === null) {
            return '';
        } else {
            return $robots->value;
        }
    }

    public static function setRobots($text)
    {
        $robots = self::findOne(self::KEY_ROBOTS);
        if ($robots === null) {
            $robots = new Config(
                [
                    'key' => self::KEY_ROBOTS,
                ]
            );
        }
        $robots->value = $text;
        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag(static::className())
            ]
        );
        return $robots->save();
    }

}
