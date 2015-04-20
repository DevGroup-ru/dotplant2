<?php

namespace app\behaviors;


use app\models\Config;
use app\models\ErrorImage;
use Yii;
use yii\base\Behavior;

class ImageExist extends Behavior
{
    public $srcAttrName = 'image_src';

    public function getSrc()
    {
        $src = $this->owner->{$this->srcAttrName};
        if (file_exists(Yii::getAlias("@webroot{$src}")) === false) {
            $src = Config::getValue(
                'image.noImage',
                'http://placehold.it/300&text=' . str_replace(' ', '+', Yii::t('app', 'No image supplied'))
            );
            if (preg_match('|http(s)?|i', $src) === 1) {
                $path = '/assets/noimage.jpg';
                file_put_contents(Yii::getAlias("@webroot$path"), file_get_contents($src));
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