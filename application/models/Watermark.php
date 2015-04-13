<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "watermark".
 *
 * @property integer $id
 * @property string $watermark_src
 */
class Watermark extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%watermark}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'watermark_src'], 'required'],
            [['id'], 'integer'],
            [['watermark_src'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'watermark_src' => Yii::t('app', 'Watermark Src'),
        ];
    }
}
