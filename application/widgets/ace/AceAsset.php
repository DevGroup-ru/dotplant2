<?php

namespace app\widgets\ace;

use yii\web\AssetBundle;

class AceAsset extends AssetBundle
{
    public $sourcePath = __DIR__;
    public $js = [
        'js/ace.js'
    ];
    public $depends = [
    ];
}
