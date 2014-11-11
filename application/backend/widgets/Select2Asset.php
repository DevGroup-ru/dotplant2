<?php

namespace app\backend\widgets;

use Yii;

class Select2Asset extends \kartik\widgets\AssetBundle
{
    public function init()
    {
        $this->setSourcePath(Yii::getAlias('@vendor/kartik-v/yii2-widgets/lib/select2'));
        $this->setupAssets('js', ['select2']);
        parent::init();
    }
}
