<?php

namespace app\backend\widgets;

use Yii;
use yii\base\Widget;
use app;

class FloatingPanel extends Widget
{
    public function run()
    {
        app\backend\assets\FrontendEditingAsset::register($this->view);
        return $this->render('floating-panel');

    }
}