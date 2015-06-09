<?php

namespace app\backend\assets;


use yii\web\AssetBundle;
use yii\web\View;

class DataRelationsAsset extends AssetBundle
{
    public $sourcePath = '@app/backend/assets/backend';
    public $js = [
        'js/dataRelations.js'
    ];

    public $jsOptions = ['position' => View::POS_HEAD];

}