<?php


namespace app\slider\sliders\slick;

use app;
use Yii;

class SlickAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/slick-carousel/slick/';

    public $css = [
        'slick.css',
        'slick-theme.css',
    ];
    public $js = [
        'slick.min.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];
} 