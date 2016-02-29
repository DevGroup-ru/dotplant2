<?php

namespace app\modules\image\models;

use app\behaviors\ImageExist;
use app\models\Object;
use app\modules\image\widgets\ImageDropzone;
use app\traits\FindById;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Exception;
use yii\caching\TagDependency;

/**
 * This is the model class for table "image".
 * @property integer $id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property string $filename
 * @property string $image_title
 * @property string $image_alt
 * @property integer $sort_order
 */
class Image extends \yii\db\ActiveRecord
{
    use FindById;

    private static $identityMap = [];

    public static function tableName()
    {
        return '{{%image}}';
    }

    public function rules()
    {
        return [
            [['sort_order', 'filename'], 'required'],
            [['object_id', 'object_model_id', 'sort_order'], 'integer'],
            [['filename', 'image_title', 'image_alt'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'image_title' => Yii::t('app', 'Image Title'),
            'image_alt' => Yii::t('app', 'Image Alt'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'filename' => Yii::t('app', 'Filename')
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => ImageExist::className(),
                'srcAttrName' => 'filename',
            ],
            [
                'class' => ActiveRecordHelper::className(),
            ],
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
                self::$identityMap[$objectId][$objectModelId] = static::find()->where(
                    [
                        'object_id' => $objectId,
                        'object_model_id' => $objectModelId,
                    ]
                )->orderBy(
                    [
                        'sort_order' => SORT_ASC,
                        'id' => SORT_ASC
                    ]
                )->all();
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
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(
                                    $object->object_class,
                                    $objectModelId
                                ),
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
     *          'filename' => 'something.png',
     *          'image_title' => 'title',
     *          'image_alt' => 'alt',
     *      ],
     *      1 => [
     *          'image_title' => 'title',
     *          'image_alt' => 'alt',
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
                    if ($new['filename'] === $current->filename && !empty($new['filename'])) {
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

            // insert new images
            foreach ($images as $key => $new) {
                if (isset($new['filename'])) {
                    if (!empty($new['filename'])) {
                        $new['filename'] = urldecode(preg_replace("~[\\?#].*$~Usi", "", $new['filename']));
                        $image_model = new Image;
                        $image_model->object_id = $object->id;
                        $image_model->object_model_id = $model->id;
                        $image_model->filename = basename($new['filename']);
                        if (preg_match("#^https?://#Us", $new['filename'])) {
                            $image_model->filename = basename(
                                preg_replace(
                                    "#^https?://[^/]#Us",
                                    "",
                                    $new['filename']
                                )
                            );
                            try {
                                $stream = fopen($new['filename'], 'r');
                                Yii::$app->getModule('image')->fsComponent->putStream($image_model->filename, $stream);
                            } catch (\Exception $e) {
                                // whoops :-(
                            }
                        } else {
                            $image_model->filename = $new['filename'];
                        }


                        $image_model->image_title = isset($new['image_title']) ? $new['image_title'] : '';
                        $image_model->image_alt = isset($new['image_alt']) ? $new['image_alt'] : '';
                        $image_model->sort_order = $key;
                        $image_model->save();
                        unset($image_model);
                    }
                }
            }

        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $defaultSize = Yii::$app->getModule('image')->defaultThumbnailSize;
        $aSize = explode('x', $defaultSize);
        $size = ThumbnailSize::findOne(['width' => $aSize[0], 'height' => $aSize[1]]);
        if ($size !== null) {
            Thumbnail::getImageThumbnailBySize($this, $size);
        }
    }

    /**
     * @param string $demand
     * @param bool|false $useWatermark
     * @return string
     * @throws Exception
     */
    public function getThumbnail($demand, $useWatermark = false)
    {
        $size = ThumbnailSize::getByDemand($demand);
        $thumb = Thumbnail::getImageThumbnailBySize($this, $size);
        /** @var string $src */
        $src = $thumb->file;
        if ($useWatermark === true) {
            try {
                $watermark = Watermark::findOne($size->default_watermark_id);
                if ($watermark !== null) {
                    $water = ThumbnailWatermark::getThumbnailWatermark($thumb, $watermark);
                    $src = $water->file;
                } else {
                    throw new Exception(Yii::t('app', 'Set watermark id'));
                }
            } catch (\Exception $e) {
                if (YII_DEBUG) {
                    throw $e;
                }
            }
        }
        return $src;
    }

    /**
     * @return mixed
     */
    public function getOriginalUrl()
    {
        return $this->file;
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $sameImages = static::findAll(['filename' => $this->filename]);
        if (empty($sameImages) === true) {
            if (Yii::$app->getModule('image')->fsComponent->has($this->filename)) {
                Yii::$app->getModule('image')->fsComponent->delete($this->filename);
            }
        }
        $thumbnails = Thumbnail::findAll(['img_id' => $this->id]);
        foreach ($thumbnails as $thumbnail) {
            $thumbnail->delete();
        }
    }
}
