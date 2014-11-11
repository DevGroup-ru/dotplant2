<?php

namespace app\widgets\form;


use yii\web\AssetBundle;

class FormAsset extends AssetBundle
{
    public $sourcePath = __DIR__;
    public $js = [
        'js/ajax-form.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
