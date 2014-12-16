<?php

namespace app\slider;


use Yii;
use yii\base\Widget;

/**
 * Class AbstractSliderWidget - all Slider implementations should extend this class!
 * @package app\slider
 */
abstract class AbstractSliderWidget extends Widget
{

    /**
     * @var \app\models\Slider slider model instance
     */
    public $slider = null;

    /**
     * @var string View file to render slider widget with - can be redefined by Slider.custom_slider_view_file
     */
    public $viewFile = "slider-widget";

    /**
     * @var string View file for each slide - can be redefined by Slider.custom_slide_view_file
     */
    public $slide_viewFile = "slide";

    /**
     * @var array Params for the JS slider - will be merged with Slider.params
     */
    public $slider_params = [];

    /**
     * @var string Advanced css classes to apply to this slider - will be appended by Slider.css_class
     */
    public $css_class = "";

    /**
     * @inheritdoc
     */
    public function run()
    {

        $this->registerAssets();

        return $this->render(
            $this->viewFile,
            [
                'slide_viewFile' => $this->slide_viewFile,
                'slider_params' => $this->slider_params,
                'css_class' => $this->css_class,
                'slider' => $this->slider,
                'id' => 'slider-'.$this->getId(),
            ]
        );
    }

    /**
     * Register slider-specific assets(js, css, etc.)
     * @return void
     */
    abstract public function registerAssets();
} 