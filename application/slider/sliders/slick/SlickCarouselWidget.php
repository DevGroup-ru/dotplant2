<?php

namespace app\slider\sliders\slick;

use app;
use Yii;

class SlickCarouselWidget extends \app\slider\AbstractSliderWidget
{

    /**
     * Register slider-specific assets(js, css, etc.)
     * @return void
     */
    public function registerAssets()
    {
        app\slider\sliders\slick\SlickAsset::register($this->getView());
    }
}