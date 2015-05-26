<?php

namespace app\backend\assets;

use yii\web\AssetBundle;
use yii\web\View;

class KoAsset extends AssetBundle
{
    public $sourcePath = '@app/backend/assets/backend';
    public $js = [
        'js/knockout.js'
    ];

    public $jsOptions = ['position' => View::POS_HEAD];
}
?>