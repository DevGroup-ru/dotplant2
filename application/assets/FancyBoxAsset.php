<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Default DotPlant2 fancybox asset bundle for frontend.
 * You can use your own, but don't forget to include CMS js and css files.
 *
 * @package app\assets
 */
class FancyBoxAsset  extends AppAsset
{

    public $sourcePath = '@app/assets/app';
    public $css = [
        'js/fancybox/jquery.fancybox.css'
    ];
    public $js = [
        'js/fancybox/jquery.fancybox.pack.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
    ];
}
