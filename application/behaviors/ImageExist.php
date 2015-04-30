<?php

namespace app\behaviors;

use app\modules\image\models\ErrorImage;
use Yii;
use yii\base\Behavior;

class ImageExist extends Behavior
{
    public $srcAttrName = 'image_src';

    public function getSrc()
    {
        $src = $this->owner->{$this->srcAttrName};
        if (file_exists(Yii::getAlias("@webroot{$src}")) === false) {
            $src = Yii::$app->getModule('image')->noImageSrc;
            if (preg_match('|http(s)?|i', $src) === 1) {
                $path = '/assets/noimage.jpg';
                file_put_contents(Yii::getAlias("@webroot$path"), file_get_contents($src));
                chmod(Yii::getAlias("@webroot$path"), 0766);
                $src = $path;
            }
            $errorImage = ErrorImage::findOne(
                ['img_id' => $this->owner->id, 'class_name' => $this->owner->className()]
            );
            if ($errorImage === null) {
                $errorImage = new ErrorImage;
                $errorImage->setAttributes(['img_id' => $this->owner->id, 'class_name' => $this->owner->className()]);
                $errorImage->save();
            }
        }
        return $src;
    }
}