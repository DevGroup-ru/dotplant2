<?php

namespace app\controllers;

use app\models\SubscribeEmail;
use Yii;
use yii\web\Controller;

class SubscribeController extends Controller
{
    public function actionAdd()
    {
        $model = new SubscribeEmail();
        if ($model->load(Yii::$app->request->post())) {
            $model->is_active = true;
            $model->save();
            Yii::$app->session->setFlash('info', 'Email saved');
        } else {
            Yii::$app->session->setFlash('warning', 'Invalid data');
        }

        return $this->render(
            'add.php',
            [
                'model' => $model
            ]
        );
    }
}
