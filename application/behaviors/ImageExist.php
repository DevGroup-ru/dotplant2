<?php

namespace app\behaviors;

use app\modules\image\components\CompileSrcInterface;
use app\modules\image\models\ErrorImage;
use Yii;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

class ImageExist extends Behavior
{
    public $srcAttrName = 'filename';

    /**
     * @return mixed
     * @throws HttpException
     */
    public function getFile()
    {
        $src = $this->owner->{$this->srcAttrName};
        if (Yii::$app->fs->has($src) === false) {
            $src = Yii::$app->getModule('image')->noImageSrc;
            $errorImage = ErrorImage::findOne(
                ['img_id' => $this->owner->id, 'class_name' => $this->owner->className()]
            );
            if ($errorImage === null) {
                $errorImage = new ErrorImage;
                $errorImage->setAttributes(['img_id' => $this->owner->id, 'class_name' => $this->owner->className()]);
                $errorImage->save();
            }
        } else {
            $fs = Yii::$app->fs;
            $components = ArrayHelper::index(Yii::$app->getModule('image')->components, 'necessary.class');
            $adapterName = ArrayHelper::getValue($components, $fs::className() . '.necessary.srcAdapter', null);
            if ($adapterName === null) {
                throw new HttpException(Yii::t('app', 'Set src compiler adapter'));
            }
            if (class_exists($adapterName) === false) {
                throw new HttpException(Yii::t('app', "Class $adapterName not found"));
            }
            $adapter = new $adapterName;
            if ($adapter instanceof CompileSrcInterface) {
                $src = $adapter->CompileSrc($src);
            } else {
                throw new HttpException(Yii::t('app', "Class $adapterName should implement CompileSrcInterface"));
            }
        }
        return $src;
    }
}