<?php

namespace app\backend\assets;


use yii\web\AssetBundle;

class LaddaAsset extends AssetBundle
{
    public $sourcePath = '@bower/ladda/dist';
    public $css = [
        'ladda-themeless.min.css',
    ];
    public $js = [
        'spin.min.js',
        'ladda.min.js',
        'ladda.jquery.min.js',

    ];

}