<?php

namespace app\models;

use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property string $filename
 * @property string $image_src
 * @property string $thumbnail_src
 * @property string $image_description
 * @property integer $sort_order
 */

class Image extends \yii\db\ActiveRecord
{
    private static $identityMap = [];

    public static function tableName()
    {
        return '{{%image}}';
    }

    public function rules()
    {
        return [
            [['image_src', 'sort_order'], 'required'],
            [['object_id','object_model_id', 'sort_order'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'image_src' => Yii::t('app', 'Image Src'),
            'image_description' => Yii::t('app', 'Image Description'),
            'sort_order' => Yii::t('app', 'Sort Order'),
        ];
    }

    /**
     * Get images by objectId
     * @param integer $objectId
     * @return Image[]
     */
    public static function getForObjectId($objectId)
    {
        $data = static::find()->where(['object_id' => $objectId])->all();
        return $data;
    }

    /**
     * Get images by objectId and objectModelId
     * @param integer $objectId
     * @param integer $objectModelId
     * @return Image[]
     */
    public static function getForModel($objectId, $objectModelId)
    {
        if (!isset(self::$identityMap[$objectId][$objectModelId])) {
            $cacheName = 'Images:' . $objectId . ':' . $objectModelId;
            self::$identityMap[$objectId][$objectModelId] = Yii::$app->cache->get($cacheName);
            if (!is_array(self::$identityMap[$objectId][$objectModelId])) {
                if (!isset(self::$identityMap[$objectId])) {
                    self::$identityMap[$objectId] = [];
                }
                self::$identityMap[$objectId][$objectModelId] = static::find()
                    ->where(
                        [
                            'object_id' => $objectId,
                            'object_model_id' => $objectModelId,
                        ]
                    )
                    ->all();
                $object = Object::findById($objectId);
                if (is_null($object)) {
                    return self::$identityMap[$objectId][$objectModelId];
                }
                Yii::$app->cache->set(
                    $cacheName,
                    self::$identityMap[$objectId][$objectModelId],
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($object->object_class, $objectModelId),
                            ],
                        ]
                    )
                );
            }
        }
        return self::$identityMap[$objectId][$objectModelId];
    }
}
