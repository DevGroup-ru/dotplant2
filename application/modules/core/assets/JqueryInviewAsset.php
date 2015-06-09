<?php

namespace app\modules\core\assets;

use yii\web\AssetBundle;

class JqueryInviewAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jquery.inview';

    /**
     * @inheritdoc
     */
    public $js = [
        'jquery.inview.min.js',
    ];
}