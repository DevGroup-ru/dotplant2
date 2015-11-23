<?php
namespace app\modules\shop\widgets\charts\assets;

use yii\web\AssetBundle;

class SalesChartsAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/shop/widgets/charts/assets';
    public $js = [
        'js/jquery.flot.cust.min.js',
        'js/jquery.flot.tooltip.min.js',
        'js/charts.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}