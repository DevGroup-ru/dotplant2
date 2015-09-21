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
        $this->attributeName = $this->property->name;
        $this->minAttribute = 'minValue[' . $this->property->id . ']';
        $this->maxAttribute = 'maxValue[' . $this->property->id . ']';
        $this->changeFlagAttribute = 'changeValue[' . $this->property->id . ']';
        $get = ArrayHelper::merge(Yii::$app->request->get(), Yii::$app->request->post());
        if ($this->selects) {
            $this->minValue = (int)array_values($this->selects)[0]['value'];
            $this->maxValue = (int)array_values($this->selects)[0]['value'];
            foreach ($this->selects as $select) {
                $select['value'] = (int)$select['value'];
                $this->minValue = $select['value'] < $this->minValue ? $select['value'] : $this->minValue;
                $this->maxValue = $select['value'] > $this->maxValue ? $select['value'] : $this->maxValue;
            }
        }
        if (isset($get['minValue']) &&
            isset($get['minValue'][$this->property->id]) &&
            is_numeric($get['minValue'][$this->property->id])
        ) {
            $this->changeFlagDefaultValue = 1;
            $this->minValueNow = $get['minValue'][$this->property->id];
        } else {
            $this->minValueNow = $this->minValue;
        }
        if (isset($get['maxValue']) &&
            isset($get['maxValue'][$this->property->id]) &&
            is_numeric($get['maxValue'][$this->property->id])
        ) {
            $this->changeFlagDefaultValue = 1;
            $this->maxValueNow = $get['maxValue'][$this->property->id];
        } else {
            $this->maxValueNow = $this->maxValue;
        }
        return parent::run();
    }
}
