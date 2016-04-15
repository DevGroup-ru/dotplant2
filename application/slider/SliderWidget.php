<?php

namespace app\slider;

use app;
use app\models\Slider;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Slider widget
 * @package app\slider\sliders
 */
class SliderWidget extends Widget
{
    /**
     * @var string Slider name
     */
    public $slider_name = null;

    /**
     * @var integer Slider ID
     */
    public $slider_id = null;

    /**
     * @var Slider|ActiveRecordHelper slider model instance
     */
    public $slider = null;

    /**
     * @var string View file to render slider widget with - can be redefined by Slider.custom_slider_view_file
     */
    public $view_file = "slider-widget";

    /**
     * @var string View file for each slide - can be redefined by Slider.custom_slide_view_file
     */
    public $slide_view_file = "slide";

    /**
     * @var array Params for the JS slider - will be merged with Slider.params
     */
    public $params = [];

    /**
     * @var string Advanced css classes to apply to this slider - will be appended by Slider.css_class
     */
    public $css_class = "";

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->slider_id === null && $this->slider_name === null && $this->slider === null) {
            throw new InvalidConfigException("Either slider, slider_id or slider_name should be set.");
        }
        if ($this->slider === null) {
            if ($this->slider_name !== null) {
                $this->slider = Yii::$app->cache->get("Slider:name:".$this->slider_name);
                if ($this->slider === false) {
                    $this->slider = Slider::find()
                        ->where(['name' => $this->slider_name])
                        ->one();
                    if ($this->slider !== null) {
                        Yii::$app->cache->set(
                            "Slider:name:" . $this->slider_name,
                            $this->slider,
                            86400,
                            new \yii\caching\TagDependency([
                                'tags' => [
                                    $this->slider->objectTag(),
                                ]
                            ])
                        );
                    }
                }
            } else {
                $this->slider = Slider::findById($this->slider_id);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->slider === null) {
            return '<!-- slider model not found -->';
        }

        $viewFile = !empty($this->slider->custom_slider_view_file) ?
            $this->slider->custom_slider_view_file :
            $this->view_file;

        $slide_viewFile = !empty($this->slider->custom_slide_view_file) ?
            $this->slider->custom_slide_view_file :
            $this->slide_view_file;

        $slider_params = Json::decode($this->slider->params);
        if (!is_array($slider_params)) {
            $slider_params = [];
        }

        $slider_params = ArrayHelper::merge($this->params, $slider_params);

        $css_class = $this->css_class . ' ' . $this->slider->css_class;

        /** @var \app\slider\AbstractSliderWidget $class_name */
        $class_name = $this->slider->handler()->slider_widget;

        return $class_name::widget([
            'viewFile' => $viewFile,
            'slide_viewFile' => $slide_viewFile,
            'slider_params' => $slider_params,
            'css_class' => $css_class,
            'slider' => $this->slider,
        ]);
    }
}
