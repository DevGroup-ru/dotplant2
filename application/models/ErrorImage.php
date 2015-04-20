<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%error_images}}".
 * @property integer $id
 * @property integer $img_id
 * @property string $class_name
 */
class ErrorImage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%error_image}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['img_id', 'class_name'], 'required'],
            [['img_id'], 'integer'],
            [['class_name'], 'string']
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
        ];
    }
}
