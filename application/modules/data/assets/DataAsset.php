<?php

namespace app\modules\data\assets;

use yii\web\AssetBundle;
use yii\web\View;

class DataAsset extends AssetBundle{

    public $sourcePath = '@app/modules/data/assets/files';

    public $js = [
        'import-export.js'
    ];

    public $jsOption = [
        'position' => View::POS_END
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}