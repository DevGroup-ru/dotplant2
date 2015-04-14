<?php

namespace app\models;

use app\widgets\image\ImageDropzone;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

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
                    ->orderBy([
                        'sort_order' => SORT_ASC,
                        'id' => SORT_ASC
                    ])
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

    /**
     * Replaces images for specified model
     * $images array format:
     * [
     *      0 => [
     *          'image_src' => 'something.png',
     *          'image_description' => 'desc',
     *      ],
     *      1 => [
     *          'image_src' => 'another-image.jpg',
     *          'image_description' => 'alt for image',
     *      ],
     * ]
     * @param \yii\db\ActiveRecord $model
     * @param array $images array of data
     * @throws \Exception
     */
    public static function replaceForModel(\yii\db\ActiveRecord $model, array $images)
    {
        $object = Object::getForClass($model->className());
        if ($object) {
            $current_images = static::getForModel($object->id, $model->id);

            // first find existing images in input array
            foreach ($current_images as $current) {
                $found = false;
                foreach ($images as $key => $new) {
                    if ($new['image_src'] === $current->image_src && !empty($new['image_src'])) {
                        $found = true;
                        $current->setAttributes($new);
                        $current->sort_order = $key;
                        $current->save();

                        // delete processed image from input array
                        unset($images[$key]);
                    }
                }
                if (!$found) {
                    $current->delete();
                }
            }
            unset($current_images);

            $dir = '/theme/resources/product-images/';
            // insert new images
            foreach ($images as $key => $new) {
                if (isset($new['image_src'])) {
                    if (!empty($new['image_src'])) {
                        $new['image_src'] = urldecode(preg_replace("~[\\?#].*$~Usi", "", $new['image_src']));
                        $image_model = new Image;
                        $image_model->object_id = $object->id;
                        $image_model->object_model_id = $model->id;
                        $image_model->filename = basename($new['image_src']);
                        if (preg_match("#^https?://#Us", $new['image_src'])) {
                            $image_model->filename = basename(preg_replace("#^https?://[^/]#Us", "",
                                    $new['image_src']));
                            try {
                                file_put_contents(
                                    Yii::getAlias('@webroot') . $dir . $image_model->filename,
                                    file_get_contents($new['image_src'])
                                );
                            } catch (\Exception $e) {
                                // whoops :-(
                            }
                            $image_model->image_src = $dir . $image_model->filename;

                        } else {
                            $image_model->image_src = $new['image_src'];
                        }
                        try {
                            //@todo rewrite
                            $image_model->thumbnail_src = $dir . ImageDropzone::saveThumbnail('@webroot' . $dir,
                                    $image_model->filename);
                        } catch (\Exception $e) {
                            // error here :-(
                        }


                        $image_model->image_description = isset($new['image_description']) ? $new['image_description'] : '';
                        $image_model->sort_order = $key;
                        $image_model->save();
                        unset($image_model);
                    }
                }
            }

        }
    }
}
