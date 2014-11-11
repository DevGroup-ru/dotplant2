<?php

namespace app\assets;

use yii\web\AssetBundle;

class JqueryUIAsset extends AssetBundle
{
    public $baseUrl = null;
    public $js = [
        '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
