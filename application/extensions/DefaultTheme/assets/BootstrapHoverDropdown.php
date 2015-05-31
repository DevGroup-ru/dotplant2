<?php

namespace app\extensions\DefaultTheme\assets;

use yii\web\AssetBundle;

/**
 * Hover dropdown plugin for bootstrap 3
 *
 * @url https://github.com/CWSpear/bootstrap-hover-dropdown
 * @package app\extensions\DefaultTheme\assets
 */
class BootstrapHoverDropdown extends AssetBundle
{

    public $sourcePath = '@bower/bootstrap-hover-dropdown';
    public $css = [
    ];
    public $js = [
        'bootstrap-hover-dropdown.min.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
    ];
}
