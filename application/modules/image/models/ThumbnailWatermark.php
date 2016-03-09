<?php

namespace app\modules\image\models;

use app\behaviors\ImageExist;
use Imagine\Image\Box;
use Yii;
use yii\base\Exception;
use yii\imagine\Image as Imagine;

/**
 * This is the model class for table "thumbnail_watermark".
 * @property integer $id
 * @property integer $thumb_id
 * @property integer $water_id
 * @property string $compiled_src
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
            [['thumb_id', 'water_id', 'compiled_src'], 'required'],
            [['thumb_id', 'water_id'], 'integer'],
            [['compiled_src'], 'string', 'max' => 255]
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => ImageExist::className(),
                'srcAttrName' => 'compiled_src',
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
            'thumb_id' => Yii::t('app', 'Thumb ID'),
            'water_id' => Yii::t('app', 'Water ID'),
            'compiled_src' => Yii::t('app', 'Compiled Src'),
        ];
    }

    /**
     * Return thumbnail width watermark or create if not exist
     * @param $thumb Thumbnail
     * @param $water Watermark
     * @return thumbnail
     */
    public static function getThumbnailWatermark($thumb, $water)
    {
        if ($thumb->thumb_path === false) {
            throw new Exception(Yii::t('app', 'Can\'t get watermark from nothing'));
        }
        $watermark = static::findOne(['thumb_id' => $thumb->id, 'water_id' => $water->id]);
        if ($watermark === null) {
            $watermark = new ThumbnailWatermark();
            $watermark->setAttributes(
                [
                    'thumb_id' => $thumb->id,
                    'water_id' => $water->id,
                ]
            );
            $watermark->compiled_src = static::createWatermark($thumb, $water);
            $watermark->save();
        }
        return $watermark;
    }

    /**
     * Create watermark in fs
     * @param $thumb Thumbnail
     * @param $water Watermark
     * @return string|false
     */
    public static function createWatermark($thumb, $water)
    {
        try {
            $thumbImagine = Imagine::getImagine()->read(
                Yii::$app->getModule('image')->fsComponent->readStream($thumb->thumb_path)
            );
            $waterImagine = Imagine::getImagine()->read(
                Yii::$app->getModule('image')->fsComponent->readStream($water->watermark_path)
            );
            $thumbSize = $thumbImagine->getSize();
            $waterSize = $waterImagine->getSize();
            // Resize watermark if it to large
            if ($thumbSize->getWidth() < $waterSize->getWidth() || $thumbSize->getHeight() < $waterSize->getHeight()) {
                $t = $thumbSize->getHeight() / $waterSize->getHeight();
                if (round($t * $waterSize->getWidth()) <= $thumbSize->getWidth()) {
                    $waterImagine->resize(new Box(round($t * $waterSize->getWidth()), $thumbSize->getHeight()));
                } else {
                    $t = $thumbSize->getWidth() / $waterSize->getWidth();
                    $waterImagine->resize(new Box($thumbSize->getWidth(), round($t * $waterSize->getHeight())));
                }
            }
            $position = [0, 0];

            if ($water->position == Watermark::POSITION_CENTER) {
                $position = [
                    round(($thumbImagine->getSize()->getWidth() - $waterImagine->getSize()->getWidth()) / 2),
                    round(($thumbImagine->getSize()->getHeight() - $waterImagine->getSize()->getHeight()) / 2)
                ];
            } else {
                $posStr = explode(' ', $water->position);
                switch ($posStr[0]) {
                    case 'TOP':
                        $position[0] = 0;
                        break;
                    case 'BOTTOM':
                        $position[0] = $thumbImagine->getSize()->getWidth() - $waterImagine->getSize()->getWidth();
                        break;
                }
                switch ($posStr[1]) {
                    case 'LEFT':
                        $position[1] = 0;
                        break;
                    case 'RIGHT':
                        $position[1] = $thumbImagine->getSize()->getHeight() - $waterImagine->getSize()->getHeight();
                        break;
                }
            }
            $tmpThumbFilePath = Yii::getAlias(
                '@runtime/' . str_replace(Yii::$app->getModule('image')->thumbnailsDirectory, '', $thumb->thumb_path)
            );
            $tmpWaterFilePath = Yii::getAlias(
                '@runtime/' . str_replace(Yii::$app->getModule('image')->watermarkDirectory, '', $water->watermark_path)
            );
            $thumbImagine->save($tmpThumbFilePath);
            $waterImagine->save($tmpWaterFilePath);

            $watermark = Imagine::watermark(
                $tmpThumbFilePath,
                $tmpWaterFilePath,
                $position
            );
            $path = Yii::$app->getModule('image')->thumbnailsDirectory;
            $fileInfo = pathinfo($thumb->thumb_path);
            $watermarkInfo = pathinfo($water->watermark_path);
            $fileName = "{$fileInfo['filename']}-{$watermarkInfo['filename']}.{$fileInfo['extension']}";
            $watermark->save(Yii::getAlias('@runtime/') . $fileName);
            $stream = fopen(Yii::getAlias('@runtime/') . $fileName, 'r+');
            Yii::$app->getModule('image')->fsComponent->putStream("$path/$fileName", $stream);
            fclose($stream);
            unlink($tmpThumbFilePath);
            unlink($tmpWaterFilePath);
            unlink(Yii::getAlias('@runtime/' . $fileName));
            return "$path/$fileName";
        } catch (Exception $e) {
            return false;
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $sameImages = static::findAll(['thumb_path' => $this->compiled_src]);
        if (empty($sameImages) === true) {
            if (Yii::$app->getModule('image')->fsComponent->has($this->compiled_src)) {
                Yii::$app->getModule('image')->fsComponent->delete($this->compiled_src);
            }
        }
    }
}
