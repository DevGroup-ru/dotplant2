<?php

namespace app\traits;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

trait LoadModel
{
    /**
     * @param $modelName
     * @param $id
     * @param bool $createIfEmptyId
     * @param bool $useCache
     * @param int $cacheLifetime
     * @param bool $throwException
     * @return ActiveRecord|null
     * @throws NotFoundHttpException
     */
    public static function loadModel(
        $modelName,
        $id,
        $createIfEmptyId = false,
        $useCache = true,
        $cacheLifetime = 3600,
        $throwException = true
    ) {
        $model = null;
        if (empty($id)) {
            if ($createIfEmptyId === true) {
                $model = new $modelName;
            } else {
                if ($throwException) {
                    throw new NotFoundHttpException;
                } else {
                    return null;
                }
            }
        }
        if ($useCache === true && $model===null) {
            $model = Yii::$app->cache->get($modelName::className() . ":" . $id);
        }
        if (!is_object($model)) {
            $model = $modelName::findOne($id);
            
            if (is_object($model) && $useCache === true) {
                Yii::$app->cache->set(
                    $modelName::className() . ":" . $id,
                    $model,
                    $cacheLifetime,
                    new TagDependency([
                        'tags' => ActiveRecordHelper::getCommonTag($modelName::className()),
                    ])
                );
            }
        }
        if (!is_object($model)) {
            if ($throwException) {
                throw new NotFoundHttpException;
            } else {
                return null;
            }
        }
        return $model;
    }
}
