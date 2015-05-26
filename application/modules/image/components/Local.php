<?php

namespace app\modules\image\components;

use Yii;

class Local implements CompileSrcInterface
{
    public function CompileSrc($path)
    {
        $fullPath = Yii::getAlias(Yii::$app->getModule('image')->components['fs']['necessary']['path'] . '/' . $path);
        return str_replace(Yii::getAlias('@webroot'), '', $fullPath);
    }
}