<?php

namespace app\slider\sliders\bootstrap3;

use app;
use Yii;

class Bootstrap3CarouselWidget extends \app\slider\AbstractSliderWidget
{

    /**
     * Register slider-specific assets(js, css, etc.)
     * @return void
     */
    public function registerAssets()
    {
        \yii\bootstrap\BootstrapPluginAsset::register($this->getView());
    }
}