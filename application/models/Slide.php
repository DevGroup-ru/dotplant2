<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

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
    use \app\traits\FindById;

    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }
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
            [['slider_id', 'sort_order', 'active'], 'integer'],
            [['image', 'link', 'custom_view_file', 'css_class'], 'string', 'max' => 255],
            [['active'], 'default', 'value'=>1],
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
            'active' => Yii::t('app', 'Active'),
        ];
    }

    /**
     * Search slides
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = static::find()
            ->where(['slider_id'=>$this->slider_id])
            ->orderBy('sort_order');
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 100,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }


        return $dataProvider;
    }
}
