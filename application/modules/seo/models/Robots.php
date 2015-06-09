<?php

namespace app\modules\seo\models;

use Yii;
/**
 * @inheritdoc
 */
class Robots extends Config
{
    const KEY_ROBOTS = 'robots.txt';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->key = self::KEY_ROBOTS;
    }

    /**
     * @return string
     */
    public static function getRobots()
    {
        /** @var Config $model */
        $model = self::getModel();
        return empty($model) ? '' : $model->value;
    }

    /**
     * @param $text
     * @return bool
     */
    public static function setRobots($text)
    {
        $model = static::getModelByKey(self::KEY_ROBOTS);
        $model = !empty($model) ?: new static();
        $model->value = $text;
        return $model->save();
    }

    /**
     * @return Config|null
     */
    static public function getModel()
    {
        return parent::getModelByKey(self::KEY_ROBOTS, Yii::$app->getModule('seo')->cacheConfig['robotsCache']['expire']);
    }

    /**
     * @inheritdoc
     */
    static public function removeCacheByKey($key = null)
    {
        return parent::removeCacheByKey(self::KEY_ROBOTS);
    }
}
