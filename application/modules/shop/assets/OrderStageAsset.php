<?php

namespace app\modules\shop\assets;

use yii\web\AssetBundle;

class OrderStageAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/shop/assets';
    public $js = [
        'js/underscore.min.js',
        'js/backbone.min.js',
        'js/geometry.js',
        'js/vectorizer.js',
        'js/joint.clean.min.js',
    ];
    public $css = [
        'css/joint.all.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
?>