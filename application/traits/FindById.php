<?php

namespace app\traits;

use Yii;
use yii\caching\TagDependency;

trait FindById
{
    /**
     * @param integer $id
     * @return \yii\db\ActiveRecord
     */
    public static function findById($id)
    {
        $cache_key = static::className() . ':' . $id;

        $model = Yii::$app->cache->get($cache_key);
        if (is_object($model) === false) {
            $model = static::findOne($id);

            if (is_object($model)) {

                Yii::$app->cache->set(
                    static::className() . ":" . $id,
                    $model,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(static::className(), $model->id),
                            ],
                        ]
                    )
                );
            }
        }

        return $model;
    }
}
