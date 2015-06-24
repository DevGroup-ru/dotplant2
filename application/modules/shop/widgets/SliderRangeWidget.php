<?php

namespace app\modules\shop\widgets;

use yii\jui\Widget;


class SliderRangeWidget extends Widget
{


    public $attributeName = 'NameAttr';

    public $minAttribute = 'min_attr';
    public $maxAttribute = 'max_attr';
    public $changeFlagAttribute = 'change_flag';
    public $changeFlagDefaultValue = 0;


    public $minValue = 0;
    public $maxValue = 99999;


    public $minValueNow = 0;
    public $maxValueNow = 99999;

    public $viewFile = 'slider-range-widget';


    public function run()
    {
        return $this->render(
            $this->viewFile,
            [
                'id' => $this->id,
                'attributeName' => $this->attributeName,
                'minAttribute' => $this->minAttribute,
                'maxAttribute' => $this->maxAttribute,
                'changeFlagAttribute' => $this->changeFlagAttribute,
                'changeFlagDefaultValue' => $this->changeFlagDefaultValue,
                'minValue' => $this->minValue,
                'maxValue' => $this->maxValue,
                'minValueNow' => $this->minValueNow,
                'maxValueNow' => $this->maxValueNow
            ]
        );
    }


}