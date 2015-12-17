<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.12.15
 * Time: 10:11
 */

namespace app\widgets;

use yii\bootstrap\Html;
use yii\bootstrap\Widget;

class TextareaWidget extends \yii\bootstrap\Widget {

    public $model;
    public $attribute;
    public $width = 600;
    public $height = 400;

    public function run() {
        parent::run();
        return Html::activeTextarea($this->model, $this->attribute, ["style" => "display: block; width: {$this->width}px; height: {$this->height}px"]);
    }
}
