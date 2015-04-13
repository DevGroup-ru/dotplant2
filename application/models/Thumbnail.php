<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "thumbnail".
 *
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
            [['id', 'img_id', 'thumb_src', 'size_id'], 'required'],
            [['id', 'img_id', 'size_id'], 'integer'],
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
}
