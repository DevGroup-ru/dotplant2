<?php

namespace app\slider\sliders\bootstrap3\models;

use app;
use app\slider\BaseSliderEditModel;
use Yii;


class EditModel extends BaseSliderEditModel
{
    public $interval = 5000;

    public function rules()
    {
        return [
            [['interval'], 'integer'],
        ];
    }
} 