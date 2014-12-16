<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%slide}}".
 *
 * @property integer $id
 * @property integer $slider_id
 * @property integer $sort_order
 * @property string $image
 * @property string $link
 * @property string $custom_view_file
 * @property string $css_class
 */
class Slide extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%slide}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slider_id', 'sort_order'], 'integer'],
            [['image', 'link', 'custom_view_file', 'css_class'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'slider_id' => Yii::t('app', 'Slider ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'image' => Yii::t('app', 'Image'),
            'link' => Yii::t('app', 'Link'),
            'custom_view_file' => Yii::t('app', 'Custom View File'),
            'css_class' => Yii::t('app', 'Css Class'),
        ];
    }
}
