<?php

namespace app\commands;

use app\modules\image\models\ErrorImage;
use app\modules\image\models\Image;
use app\models\Thumbnail;
use app\models\ThumbnailSize;
use Yii;
use yii\console\Controller;

class ImagesController extends Controller
{
    public function actionRecreateThumbnails($idList)
    {
        $ids = explode(',', $idList);
        $sizes = ThumbnailSize::find()->all();
        foreach ($ids as $imageId) {
            $image = Image::findOne($imageId);
            if ($image !== null) {
                foreach ($sizes as $size) {
                    Thumbnail::createThumbnail($image, $size);
                }
            }
        }
    }

    public function actionCheckBroken()
    {
        $images = Image::find()->all();
        ErrorImage::deleteAll();
        foreach ($images as $image) {
            $src = $image->image_src;
            if (file_exists(Yii::getAlias("@webroot{$src}")) === false) {
                $errorImage = new ErrorImage;
                $errorImage->setAttributes(['img_id' => $image->id, 'class_name' => $image->className()]);
                $errorImage->save();
            }
        }
    }
}