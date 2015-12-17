<?php

namespace app\widgets;

use yii\bootstrap\Html;
use yii\bootstrap\Widget;

class TextareaWidget extends Widget
{

    public $model;
    public $attribute;
    public $htmlOptions;

    public function run()
    {
        parent::run();
        return Html::activeTextarea(
            $this->model,
            $this->attribute,
            $this->htmlOptions
        );
    }
}
