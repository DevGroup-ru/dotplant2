<?php

namespace app\models;

use Yii;
use yii\helpers\VarDumper;
use yii\imagine\Image as Imagine;
use yii\web\UploadedFile;

/**
 * This is the model class for table "thumbnail".
 * @property integer $id
 * @property integer $img_id
 * @property string $thumb_src
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
            [['img_id', 'thumb_src', 'size_id'], 'required'],
            [['img_id', 'size_id'], 'integer'],
            [['thumb_src'], 'string', 'max' => 255]
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
            'thumb_src' => Yii::t('app', 'Thumb Src'),
            'size_id' => Yii::t('app', 'Size ID'),
        ];
    }

    /**
     * Return thumb of image by size or create if not exist
     * @param $image Image
     * @param $size ThumbnailSize
     * @return static
     */
    public static function getImageThumbnailBySize($image, $size)
    {
        /**
         * @todo cache
         */
        $thumb = static::findOne(['img_id' => $image->id, 'size_id' => $size->id]);
        if ($thumb === null) {
            $thumb = new Thumbnail;
            $thumb->setAttributes(
                [
                    'image_id' => $image->id,
                    'size_id' => $size->id,
                ]
            );
            $thumb->thumb_src = static::createThumbnail($image, $size);
            $thumb->save();
        }
        return $thumb;
    }

    /**
     * Create thumbnail in fs
     * @param $image Image
     * @param $size ThumbnailSize
     * @return string
     */
    public static function createThumbnail($image, $size)
    {
        $thumb = Imagine::thumbnail('@webroot' . $image->image_src, $size->width, $size->height);
        $path = Config::getValue('thumbnailPath', '/theme/resources/product-images/thumbnail');
        $file_info = pathinfo($image->image_src);

        $src = "$path/{$file_info['filename']}-{$size->width}x{$size->height}.{$file_info['extension']}";
        $thumb->save(Yii::getAlias('@webroot') . $src);
        return $src;
    }
}
