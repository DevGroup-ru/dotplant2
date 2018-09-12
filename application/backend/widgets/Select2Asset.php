<?php

namespace app\backend\widgets;

use Yii;

class Select2Asset extends \kartik\base\AssetBundle
{
    public function init()
    {
        $this->setSourcePath(Yii::getAlias('@kartik/select2/assets'));
        $this->setupAssets('js', ['js/select2.full']);
        parent::init();
    }
}
