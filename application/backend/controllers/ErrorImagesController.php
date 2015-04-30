<?php

namespace app\backend\controllers;


use app\modules\image\models\ErrorImage;
use app\modules\image\models\Image;
use app\models\Object;
use yii\filters\AccessControl;
use yii\web\Controller;

class ErrorImagesController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['content manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $objects = [];
        $errors = ErrorImage::find()->all();
        foreach ($errors as $error) {
            $image = Image::findOne($error->img_id);
            $object = Object::findOne($image->object_id);
            $item = call_user_func([$object->object_class, 'findOne'], $image->object_model_id);
            $objects[$object->name][] = $item;
        }
        return $this->render('index', ['objects' => $objects]);
    }
}