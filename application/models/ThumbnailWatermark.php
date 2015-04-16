<?php

namespace app\models;

use Imagine\Image\Box;
use Yii;
use yii\imagine\Image as Imagine;

/**
 * This is the model class for table "thumbnail_watermark".
 * @property integer $id
 * @property integer $thumb_id
 * @property integer $water_id
 * @property string $src
 */
class ThumbnailWatermark extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%thumbnail_watermark}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['thumb_id', 'water_id', 'src'], 'required'],
            [['thumb_id', 'water_id'], 'integer'],
            [['src'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'thumb_id' => Yii::t('app', 'Thumb ID'),
            'water_id' => Yii::t('app', 'Water ID'),
            'src' => Yii::t('app', 'Src'),
        ];
    }

    /**
     * Return thumbnail width watermark or create if not exist
     * @param $thumb Thumbnail
     * @param $water Watermark
     * @return static
     */
    public static function getThumbnailWatermark($thumb, $water)
    {
        /**
         * @todo cache
         */
        $watermark = static::findOne(['thumb_id' => $thumb->id, 'water_id' => $water->id]);
        if ($watermark === null) {
            $watermark = new ThumbnailWatermark();
            $watermark->setAttributes(
                [
                    'thumb_id' => $thumb->id,
                    'water_id' => $water->id,
                ]
            );
            $watermark->src = static::createWatermark($thumb, $water);
            $watermark->save();
        }
        return $watermark;
    }

    /**
     * Create watermark in fs
     * @param $thumb Thumbnail
     * @param $water Watermark
     * @return string
     */
    public static function createWatermark($thumb, $water)
    {
        $thumbImage = Imagine::getImagine()->open(Yii::getAlias('@webroot' . $thumb->thumb_src));
        $waterImage = Imagine::getImagine()->open(Yii::getAlias('@webroot' . $water->watermark_src));
        $thumbSize = $thumbImage->getSize();
        $waterSize = $waterImage->getSize();
        $watermark_src = '@webroot' . $water->watermark_src;
        // Resize watermark if it to large
        if ($thumbSize->getWidth() < $waterSize->getWidth() || $thumbSize->getHeight() < $waterSize->getHeight()) {
            $t = $thumbSize->getHeight() / $waterSize->getHeight();
            $watermark_src = '@runtime' . str_replace(
                    Config::getValue('image.waterDir', '/theme/resources/product-images/watermark'),
                    '',
                    $water->watermark_src
                );
            if (round($t * $waterSize->getWidth()) <= $thumbSize->getWidth()) {
                $waterImage->resize(new Box(round($t * $waterSize->getWidth()), $thumbSize->getHeight()))->save(
                    Yii::getAlias($watermark_src)
                );
            } else {
                $t = $thumbSize->getWidth() / $waterSize->getWidth();
                $waterImage->resize(new Box($thumbSize->getWidth(), round($t * $waterSize->getHeight())))->save(
                    Yii::getAlias($watermark_src)
                );
            }

        }
        $watermark = Imagine::watermark('@webroot' . $thumb->thumb_src, $watermark_src);
        $path = Config::getValue('image.thumbDir', '/theme/resources/product-images/thumbnail');
        $file_info = pathinfo($thumb->thumb_src);
        $watermark_info = pathinfo($water->watermark_src);
        $src = "$path/{$file_info['filename']}-{$watermark_info['filename']}.{$file_info['extension']}";
        $watermark->save(Yii::getAlias('@webroot') . $src);
        return $src;
    }
}
