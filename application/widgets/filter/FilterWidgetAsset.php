<?php

namespace app\widgets\filter;

use yii\web\AssetBundle;

class FilterWidgetAsset extends AssetBundle
{
    public $sourcePath = __DIR__;
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    public $js = [
        'js/filter.js',
    ];
}
