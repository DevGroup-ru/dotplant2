<?php


namespace app\slider\sliders\slick;

use app;
use Yii;

class SlickAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/slick-carousel/';

    public $css = [
        'slick/slick.css',
    ];
    public $js = [
        'slick/slick.min.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];
} 