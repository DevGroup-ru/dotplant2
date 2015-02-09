<?php

namespace app\backend\assets;

use Yii;
use yii\web\AssetBundle;

class FrontendEditingAsset extends AssetBundle
{
    public $sourcePath = '@app/backend/assets/frontend-editing';

    public $css = [
        'css/floating-panel.css',
    ];

    public $publishOptions = [
        'forceCopy' => true
    ];
}