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


            if (isset($get[$this->minAttribute]) && is_numeric($get[$this->minAttribute])) {
                $this->changeFlagDefaultValue = 1;
                $this->minValueNow = $get[$this->minAttribute];
            } else {
                $this->minValueNow = $this->minValue;
            }

            if (isset($get[$this->maxAttribute]) && is_numeric($get[$this->maxAttribute])) {
                $this->changeFlagDefaultValue = 1;
                $this->maxValueNow = $get[$this->maxAttribute];
            } else {
                $this->maxValueNow = $this->maxValue;
            }

        }


        return parent::run();
    }


} 