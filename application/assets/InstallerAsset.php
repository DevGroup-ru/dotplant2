<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for DotPlant2 installer
 *
 * @package app\assets
 */
class InstallerAsset extends AssetBundle
{

    public $sourcePath = '@app/assets/installer';
    public $css = [
        'css/installer.css'
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
        'yii\jui\JuiAsset',
        '\kartik\icons\FontAwesomeAsset',
    ];
}
