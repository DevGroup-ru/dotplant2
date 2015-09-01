<?php

namespace app\extensions\DefaultTheme\widgets\LastViewedProducts;

use app\widgets\LastViewedProducts;
use Yii;
use app\extensions\DefaultTheme\components\BaseWidget;

class Widget extends BaseWidget
{
    public $elementNumber = 3;
    public $title = "Recently Viewed Products";
    public $viewFileWidget = 'lastviewedproducts\main-view';
    public $widgetClass = '';

    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    public function widgetRun()
    {
        $widget = empty($this->widgetClass) ? LastViewedProducts::className() : $this->widgetClass;
        return $widget::widget([
            'elementNumber' => $this->elementNumber,
            'title' => $this->title,
            'viewFile' => $this->viewFileWidget
        ]);
    }
}