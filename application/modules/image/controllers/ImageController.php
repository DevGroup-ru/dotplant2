<?php

namespace app\modules\image\controllers;


use app\modules\image\models\Image;
use app\modules\image\models\Thumbnail;
use app\modules\image\models\ThumbnailWatermark;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ImageController extends Controller
{
    public function actionImage($fileName)
    {
        $response = Yii::$app->getResponse();
        $image = Image::findOne(['filename' => $fileName]);
        if ($image === null) {
            throw new NotFoundHttpException;
        }

        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-type', Yii::$app->fs->getMimetype($image->filename));
        return $image->file;
    }

    public function actionThumbnail($fileName)
    {
        $response = Yii::$app->getResponse();
        $thumb = Thumbnail::findOne(['thumb_path' => $fileName]);
        if ($thumb === null) {
            throw new NotFoundHttpException;
        }

        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-type', Yii::$app->fs->getMimetype($thumb->thumb_path));
        return $thumb->file;
    }

    public function actionThumbnailWatermark($fileName)
    {
        $response = Yii::$app->getResponse();
        $water = ThumbnailWatermark::findOne(['compiled_src' => $fileName]);
        if ($water === null) {
            throw new NotFoundHttpException;
        }

        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-type', Yii::$app->fs->getMimetype($water->compiled_src));
        return $water->file;
    }

}