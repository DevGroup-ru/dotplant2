<?php
namespace app\modules\seo\assets;

use yii\web\AssetBundle;

class EcommerceAnalyticsAssets extends AssetBundle
{
    public $sourcePath = '@app/modules/seo/assets';

    public $js = [
        'js/ec-analytics.js',
    ];

    public $css = [
    ];

    public $depends = [
        'app\assets\AppAsset',
    ];
}
