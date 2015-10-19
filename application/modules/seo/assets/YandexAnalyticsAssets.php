<?php
namespace app\modules\seo\assets;

use yii\web\AssetBundle;

class YandexAnalyticsAssets extends AssetBundle
{
    public $sourcePath = '@app/modules/seo/assets';

    public $js = [
        'js/ya-analytics.js',
    ];

    public $css = [
    ];

    public $depends = [
        'app\assets\AppAsset',
    ];
}
