<?php

namespace app\backend\assets;

use yii\web\AssetBundle;
use yii\web\View;

class NavigationAsset extends AssetBundle
{
    public $sourcePath = '@app/backend/assets/backend';

    public $js = [
        'js/navigation.js'
    ];

    public $jsOption = [
        'position' => View::POS_END
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}