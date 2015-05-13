<?php

namespace app\behaviors;

use app\modules\image\models\ErrorImage;
use Yii;
use yii\base\Behavior;

class ImageExist extends Behavior
{
    public $srcAttrName = 'filename';

    public function getFile()
    {
        $src = $this->owner->{$this->srcAttrName};
        if (Yii::$app->fs->has($src) === false) {
            $src = Yii::$app->getModule('image')->noImageSrc;
            $stream = file_get_contents($src);
            $errorImage = ErrorImage::findOne(
                ['img_id' => $this->owner->id, 'class_name' => $this->owner->className()]
            );
            if ($errorImage === null) {
                $errorImage = new ErrorImage;
                $errorImage->setAttributes(['img_id' => $this->owner->id, 'class_name' => $this->owner->className()]);
                $errorImage->save();
            }
        } else {
            $stream = Yii::$app->fs->readStream($src);
        }
        return stream_get_contents($stream);
    }
}