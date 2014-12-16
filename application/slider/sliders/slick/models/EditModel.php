<?php

namespace app\slider\sliders\slick\models;

use app;
use app\slider\BaseSliderEditModel;
use Yii;


class EditModel extends BaseSliderEditModel
{
    public $autoplaySpeed = 3000;
    public $autoplay = true;
    public $dots = true;
    public $fade = true;
    public $speed = 300;
    public $prevArrow = null;
    public $nextArrow = null;

    public function rules()
    {
        return [
            [['autoplaySpeed', 'speed'], 'integer'],
            [['autoplay', 'dots', 'fade'], 'boolean'],
            [['autoplay', 'dots', 'fade'], 'filter', 'filter'=>'boolval'],
            [['nextArrow', 'prevArrow'], 'filter', 'filter'=> function($value) {
                return empty($value) ? null : $value;
            }],
        ];
    }
} 