<?php

namespace app\widgets\dropzone;

use yii\web\AssetBundle;

class DropZoneAsset extends AssetBundle
{
    public $sourcePath = '@app/widgets/dropzone/assets/dropzone/downloads';
    public $css = [
        'css/dropzone.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
    ];
    public $js = [
        'dropzone.min.js',
    ];
}
