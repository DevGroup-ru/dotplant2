<?php

namespace app\extensions\DefaultTheme\assets;

use yii\web\AssetBundle;

/**
 * Default DotPlant2 asset bundle for default theme based on Bootstrap 3.
 * You can use your own, but don't forget to include CMS js and css files.
 *
 * @package app\extensions\DefaultTheme\assets
 */
class DefaultThemeAsset extends AssetBundle
{

    public $sourcePath = '@app/extensions/DefaultTheme/assets/theme';
    public $css = [
        'css/default-theme.css'
    ];
    public $js = [

    ];
    public $depends = [
        'app\assets\AppAsset',
        'app\extensions\DefaultTheme\assets\BootstrapHoverDropdown',
    ];
}
