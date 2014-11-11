<?php

namespace app\backend\widgets\flushcache;

use yii\web\AssetBundle as AssetBundle;

class FlushCacheButtonAsset extends AssetBundle
{
    public $sourcePath = '@app/backend/widgets/flushcache';
    public $js = [
        'js/flushcache.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
