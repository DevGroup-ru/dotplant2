<?php

namespace app\controllers;

use Yii;
use app\models\SubscribeEmail;
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
