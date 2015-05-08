<?php

namespace app\modules\review\controllers;

use app\components\Controller;
use app\modules\review\traits\ProcessReviews;
use Yii;
use yii\web\HttpException;

class ProcessController extends Controller
{
    use ProcessReviews;

    public function actionIndex($object_id, $object_model_id, $returnUrl = '/')
    {
        if (Yii::$app->request->isPost) {
            $this->processReviews($object_id, $object_model_id);
            $this->redirect($returnUrl);
        } else {
            throw new HttpException(403);
        }


    }


}