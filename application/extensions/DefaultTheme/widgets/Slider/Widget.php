<?php

namespace app\extensions\DefaultTheme\widgets\Slider;


use app\extensions\DefaultTheme\components\BaseWidget;
use app\slider\SliderWidget;
use yii\helpers\Html;

class Widget extends BaseWidget
{
    public $sliderId = 1;
    public $inContainer = true;
    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    public function widgetRun()
    {
        $slider = SliderWidget::widget([
            'slider_id' => $this->sliderId,
        ]);

        if ($this->inContainer) {
            $slider = "<div class=\"container\"><div class=\"row\"><div class=\"col-md-12\">$slider</div></div></div>";
        }

        return $slider;
    }
}