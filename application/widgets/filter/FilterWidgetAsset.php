<?php

namespace app\widgets\filter;

use yii\web\AssetBundle;

class FilterWidgetAsset extends AssetBundle
{
    public $sourcePath = '@app/widgets/filter/assets_sources';

    public $depends = [
        'yii\web\JqueryAsset',
    ];
    public $js = [
        'js/filter.js',
    ];
}
