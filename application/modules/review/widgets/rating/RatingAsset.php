<?php

namespace app\modules\review\widgets\rating;

use yii\web\AssetBundle;

class RatingAsset extends AssetBundle
{
    public $sourcePath = __DIR__;
    public $css = [
        'css/bootstrap-rating.css',
    ];
    public $js = [
        'js/bootstrap-rating.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
