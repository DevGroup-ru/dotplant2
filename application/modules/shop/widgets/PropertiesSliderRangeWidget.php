<?php

namespace app\modules\shop\widgets;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * @property \app\models\Property $property
 */
class PropertiesSliderRangeWidget extends SliderRangeWidget
{

    public $property;
    public $selects = [];
    public $categoryId;

    public function run()
    {
        $this->minValue = (int)$this->minValue;
        $this->maxValue = (int)$this->maxValue;
        $this->attributeName = $this->property->name;
        $this->minAttribute = 'minValue[' . $this->property->id . ']';
        $this->maxAttribute = 'maxValue[' . $this->property->id . ']';
        $this->changeFlagAttribute = 'changeValue[' . $this->property->id . ']';
        $get = ArrayHelper::merge(Yii::$app->request->get(), Yii::$app->request->post());
        if (isset($get['minValue']) &&
            isset($get['minValue'][$this->property->id]) &&
            is_numeric($get['minValue'][$this->property->id]) &&
            $this->minValue !== (int)$get['minValue'][$this->property->id]
        ) {
            $this->changeFlagDefaultValue = 1;
            $this->minValueNow = (int)$get['minValue'][$this->property->id];
        } else {
            $this->minValueNow = $this->minValue;
        }
        if (isset($get['maxValue']) &&
            isset($get['maxValue'][$this->property->id]) &&
            is_numeric($get['maxValue'][$this->property->id]) &&
            $this->maxValue !== (int)$get['maxValue'][$this->property->id]
        ) {
            $this->changeFlagDefaultValue = 1;
            $this->maxValueNow = (int)$get['maxValue'][$this->property->id];
        } else {
            $this->maxValueNow = $this->maxValue;
        }
        return parent::run();
    }
}
