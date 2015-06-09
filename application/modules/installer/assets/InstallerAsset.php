<?php

namespace app\modules\installer\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for DotPlant2 installer
 *
 * @package app\modules\installer\assets
 */
class InstallerAsset extends AssetBundle
{

    public $sourcePath = '@app/modules/installer/assets/installer';
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
        'app\backend\assets\LaddaAsset',
    ];
}
