<?php

namespace app\widgets;

use yii\bootstrap\Html;
use yii\bootstrap\Widget;

class TextareaWidget extends Widget
{

    public $model;
    public $attribute;
    public $defaultHtmlOptions = [
        'style' => [
            'width: 600px; height: 400px'
        ]
    ];
    public $htmlOptions;

    public function run()
    {
        parent::run();
        $this->htmlOptions = array_merge($this->defaultHtmlOptions, $this->htmlOptions);
        return Html::activeTextarea(
            $this->model,
            $this->attribute,
            $this->htmlOptions
        );
    }
}
