<?php

namespace app\commands;

use app\modules\image\models\ErrorImage;
use app\modules\image\models\Image;
use app\modules\image\models\Thumbnail;
use app\modules\image\models\ThumbnailSize;
use Yii;
use yii\console\Controller;

class ImagesController extends Controller
{
    public function actionRecreateThumbnails($idList = null, $showProgress = false)
    {
        /** @var ThumbnailSize[] $sizes */
        $sizes = ThumbnailSize::find()->all();
        /** @var Image[] $images */
        if (is_null($idList)) {
            $images = Image::find()->all();
        } else {
            $ids = explode(',', $idList);
            $images = Image::findAll(['id' => $ids]);
        }
        foreach ($images as $image) {
            if ($showProgress) {
                echo "Image: {$image->id}\n";
            }
            if ($image !== null) {
                foreach ($sizes as $size) {
                    Thumbnail::getImageThumbnailBySize($image, $size);
                }
            }
        }
    }

    public function actionCheckBroken()
    {
        /** @var Image[] $images */
        $images = Image::find()->all();
        ErrorImage::deleteAll();
        foreach ($images as $image) {
            $src = $image->filename;
            if (Yii::$app->fs->has($src) === false) {
                $errorImage = new ErrorImage;
                $errorImage->setAttributes(['img_id' => $image->id, 'class_name' => $image->className()]);
                $errorImage->save();
            }
        }
    }
}
