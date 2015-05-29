<?php

namespace app\traits;

use Yii;
use yii\caching\TagDependency;

/**
 * Trait IdentityMap is similar to FindById trait but also uses identity map pattern.
 * @package app\traits
 */
trait IdentityMap
{
    public static $identity_map = [];

    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            $cache_key = static::className() . ':' . $id;

            static::$identity_map[$id] = Yii::$app->cache->get($cache_key);
            if (static::$identity_map[$id] === false) {
                static::$identity_map[$id] = static::findOne($id);

                if (is_object(static::$identity_map[$id])) {

                    Yii::$app->cache->set(
                        static::className() . ":" . $id,
                        static::$identity_map[$id],
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(static::className(), static::$identity_map[$id]->id),
                                ],
                            ]
                        )
                    );
                }
            }

            return static::$identity_map[$id];
        }
        return static::$identity_map[$id];
    }
}