<?php
namespace app\modules\seo\assets;

use yii\web\AssetBundle;

class GoogleAnalyticsAssets extends AssetBundle
{
    public $sourcePath = '@app/modules/seo/assets';

    public $js = [
        'js/ga-analytics.js',
    ];

    public $css = [
    ];

    public $depends = [
        'app\assets\AppAsset',
    ];
}
