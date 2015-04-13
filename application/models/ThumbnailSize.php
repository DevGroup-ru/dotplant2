<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "thumbnail_size".
 *
 * @property integer $id
 * @property integer $width
 * @property integer $height
 */
class ThumbnailSize extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%thumbnail_size}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'width', 'height'], 'required'],
            [['id', 'width', 'height'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
        ];
    }
}
