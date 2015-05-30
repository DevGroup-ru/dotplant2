<?php

namespace app\modules\image\components;


use Yii;

class Awss3 implements CompileSrcInterface
{
    public function CompileSrc($path)
    {
        $bucket = Yii::$app->getModule('image')->fsComponent->bucket;
        return "http://$bucket.s3.amazonaws.com/$path";
    }
}