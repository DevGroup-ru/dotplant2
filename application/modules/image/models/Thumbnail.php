<?php

namespace app\modules\image\models;

use app\behaviors\ImageExist;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;
use Yii;
use yii\helpers\ArrayHelper;
use yii\imagine\Image as Imagine;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "{{%thumbnail}}".
 * @property integer $id
 * @property integer $img_id
 * @property string $thumb_path
 * @property integer $size_id
 */
class Thumbnail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%thumbnail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['img_id', 'thumb_path', 'size_id'], 'required'],
            [['img_id', 'size_id'], 'integer'],
            [['thumb_path'], 'string', 'max' => 255]
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => ImageExist::className(),
                'srcAttrName' => 'thumb_path',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'img_id' => Yii::t('app', 'Img ID'),
            'thumb_path' => Yii::t('app', 'Thumb Src'),
            'size_id' => Yii::t('app', 'Size ID'),
        ];
    }

    /**
     * Return thumb of image by size or create if not exist
     * @param $image Image
     * @param $size ThumbnailSize
     * @return Thumbnail
     */
    public static function getImageThumbnailBySize($image, $size)
    {
        $cacheKey = 'thumbBySize:'.$image->id . ';'.$size->id;
        $thumb = Yii::$app->cache->get($cacheKey);
        if ($thumb === false) {
            $thumb = static::findOne(['img_id' => $image->id, 'size_id' => $size->id]);
            if ($thumb === null) {
                $thumb = new Thumbnail;
                $thumb->setAttributes(
                    [
                        'img_id' => $image->id,
                        'size_id' => $size->id,
                    ]
                );
                $thumb->thumb_path = static::createThumbnail($image, $size);
                $thumb->save();
            }
            Yii::$app->cache->set($cacheKey, $thumb, 86400);
        }
        return $thumb;
    }

    /**
     * Create thumbnail in fs
     * @param $image Image
     * @param $size ThumbnailSize
     * @return string|false
     */
    public static function createThumbnail($image, $size)
    {
        try {
            /** @var Filesystem $fs */
            $fs = Yii::$app->getModule('image')->fsComponent;

            $file = Imagine::getImagine()->read($fs->readStream($image->filename));
            /** @var ImageInterface $thumb */
            if($size->resize_mode == ThumbnailSize::RESIZE){
                $thumb = $file->resize(new Box($size->width, $size->height));
            }else{
                $thumb = $file->thumbnail(new Box($size->width, $size->height), $size->resize_mode);    
            }
            
            $path = Yii::$app->getModule('image')->thumbnailsDirectory;

            if (!preg_match('#^(?<name>.+)\.(?<ext>[^\.]+)$#', $image->filename, $fileInfo)) {
                return false;
            }

            $stream = $thumb->get($fileInfo['ext'], ['quality' => $size->quality]);
            $src = "$path/{$fileInfo['name']}-{$size->width}x{$size->height}.{$fileInfo['ext']}";
            $fs->put($src, $stream);
            return $src;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     * @param bool $insert
     * @param array $changedAttributes
     * @throws BadRequestHttpException
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (Yii::$app->getModule('image')->useWatermark == 1) {
            /** @var ThumbnailSize $size */
            $size = ThumbnailSize::findOne(ArrayHelper::getValue($this, 'size_id', 0));
            if ($size !== null) {
                $watermark = Watermark::findOne($size->default_watermark_id);
                if ($watermark !== null) {
                    ThumbnailWatermark::getThumbnailWatermark($this, $watermark);
                }
            } else {
                throw new BadRequestHttpException(Yii::t('app', 'Set thumbnail size'));
            }
        }
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $sameImages = static::findAll(['thumb_path' => $this->thumb_path]);
        if (empty($sameImages) === true) {
            if (Yii::$app->getModule('image')->fsComponent->has($this->thumb_path)) {
                Yii::$app->getModule('image')->fsComponent->delete($this->thumb_path);
            }
            $thumbnailWatermarks = ThumbnailWatermark::findAll(['thumb_id' => $this->id]);
            foreach ($thumbnailWatermarks as $thumbnailWatermark) {
                $thumbnailWatermark->delete();
            }
        }
    }
}
