<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Default DotPlant2 asset bundle for frontend.
 * You can use your own, but don't forget to include CMS js and css files.
 *
 * @package app\assets
 */
class AppAsset extends AssetBundle
{

    public $sourcePath = '@app/assets/app';
    public $css = [
        'css/dotplant.css'
    ];
    public $js = [
        'js/main.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\web\JqueryAsset',
        'yii\validators\ValidationAsset',
        'yii\widgets\ActiveFormAsset',
        '\kartik\icons\FontAwesomeAsset',
    ];
}
