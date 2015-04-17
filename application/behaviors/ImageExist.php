<?php

namespace app\behaviors;


use app\models\Config;
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
            //@todo add log
        }
        return $src;
    }
}