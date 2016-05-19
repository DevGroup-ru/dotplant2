<?php

namespace app\models;


use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%slider}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $slider_handler_id
 * @property integer $image_width
 * @property integer $image_height
 * @property integer $resize_big_images
 * @property integer $resize_small_images
 * @property string $css_class
 * @property string $params
 * @property string $custom_slider_view_file
 * @property string $custom_slide_view_file
 */
class Slider extends \yii\db\ActiveRecord
{
    private $_slides = null;

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
        return '{{%slider}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slider_handler_id', 'image_width', 'image_height', 'resize_big_images', 'resize_small_images'], 'integer'],
            [['params'], 'string'],
            [['name', 'css_class', 'custom_slider_view_file', 'custom_slide_view_file'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'slider_handler_id' => Yii::t('app', 'Slider Handler ID'),
            'image_width' => Yii::t('app', 'Image Width'),
            'image_height' => Yii::t('app', 'Image Height'),
            'resize_big_images' => Yii::t('app', 'Resize Big Images'),
            'resize_small_images' => Yii::t('app', 'Resize Small Images'),
            'css_class' => Yii::t('app', 'Css Class'),
            'params' => Yii::t('app', 'Params'),
            'custom_slider_view_file' => Yii::t('app', 'Custom Slider View File'),
            'custom_slide_view_file' => Yii::t('app', 'Custom Slide View File'),
        ];
    }

    /**
     * Returns corresponding slides with cache support(not real relation!)
     * @return Slide[]
     */
    public function getSlides($onlyActive = false)
    {
        if ($this->_slides === null) {
            $this->_slides = Yii::$app->cache->get("Slides:" . $this->id);
            if (!is_array($this->_slides)) {
                $this->_slides = Slide::find()
                    ->where(['slider_id' => $this->id])
                    ->orderBy('sort_order ASC')
                    ->all();
                Yii::$app->cache->set(
                    "Slides:" . $this->id,
                    $this->_slides,
                    86400,
                    new \yii\caching\TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getObjectTag(Slider::className(), $this->id)
                        ]
                    ])
                );
            }
        }
        if ($onlyActive === true) {
            $activeSlides = [];
            foreach ($this->_slides as $slide) {
                if ($slide->active) {
                    $activeSlides[] = $slide;
                }
            }
            return $activeSlides;
        } else {
            return $this->_slides;
        }
    }

    /**
     * Returns handler model for this slider
     * @return SliderHandler|null
     */
    public function handler()
    {
        return SliderHandler::findBySliderId($this->slider_handler_id);
    }
}
