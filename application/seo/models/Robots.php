<?php

namespace app\seo\models;

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
        $robots = \Yii::$app->getCache()->get(\Yii::$app->getModule('seo')->cacheConfig['robotsCache']['name']);
        if (!$robots) {
            $robots = self::findOne(self::KEY_ROBOTS);
            \Yii::$app->getCache()->set(
                \Yii::$app->getModule('seo')->cacheConfig['robotsCache']['name'],
                $robots,
                \Yii::$app->getModule('seo')->cacheConfig['robotsCache']['expire']
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
        return $robots->save();
    }
}
