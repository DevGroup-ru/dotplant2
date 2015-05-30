<?php

namespace app\modules\image\components;


use Yii;

class Ftp implements CompileSrcInterface
{
    public function CompileSrc($path)
    {
        return Yii::$app->getModule('image')->fsComponent->host . '/' . $path;
    }
}