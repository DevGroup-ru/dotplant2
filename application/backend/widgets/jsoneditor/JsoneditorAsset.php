<?php

namespace app\backend\widgets\jsoneditor;

use yii\web\AssetBundle;

class JsoneditorAsset extends AssetBundle
{
    public $sourcePath = __DIR__;
    public $js = [
        'assets/jsoneditor.min.js'
    ];
    public $css = [
        'assets/jsoneditor.min.css'
    ];
}
