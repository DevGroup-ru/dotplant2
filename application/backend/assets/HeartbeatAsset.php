<?php


namespace app\backend\assets;


use yii\web\AssetBundle;

class HeartbeatAsset extends AssetBundle
{
    public $sourcePath = '@app/backend/assets/backend';
    public $js = [
        'js/heartbeat.js'
    ];
}