<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "thumbnail_watermark".
 *
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
}
